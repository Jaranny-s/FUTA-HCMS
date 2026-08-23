/*
|--------------------------------------------------------------------------
| PRINCIPAL PATIENT AJAX SEARCH
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchInput =
            document.getElementById(
                'principal_patient_search'
            );

        const resultsBox =
            document.getElementById(
                'principal-search-results'
            );

        const selectedBox =
            document.getElementById(
                'selected-principal-patient'
            );

        const hiddenInput =
            document.getElementById(
                'principal_patient_public_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Stop if this page does not contain the principal search
        |--------------------------------------------------------------------------
        */

        if (
            !searchInput ||
            !resultsBox ||
            !selectedBox ||
            !hiddenInput
        ) {

            return;

        }


        const currentPatientId =
            parseInt(
                searchInput.dataset.currentId,
                10
            );


        const searchUrl =
            searchInput.dataset.searchUrl;


        /*
        |--------------------------------------------------------------------------
        | Basic configuration check
        |--------------------------------------------------------------------------
        */

        if (
            !currentPatientId ||
            !searchUrl
        ) {

            console.error(
                'Principal patient search is missing current patient ID or search URL.'
            );

            return;

        }


        let searchTimer = null;

        let activeController = null;


        /*
        |--------------------------------------------------------------------------
        | Hide results
        |--------------------------------------------------------------------------
        */

        function hideResults() {

            resultsBox.innerHTML = '';

            resultsBox.style.display = 'none';

        }


        /*
        |--------------------------------------------------------------------------
        | Clear selected principal
        |--------------------------------------------------------------------------
        */

        function clearSelectedPrincipal() {

            hiddenInput.value = '';

            selectedBox.innerHTML = '';

        }


        /*
        |--------------------------------------------------------------------------
        | Display selected principal
        |--------------------------------------------------------------------------
        */

        function displaySelectedPrincipal(patient) {

            let fullName =
                patient.surname +
                ', ' +
                patient.first_name;


            if (
                patient.middle_name &&
                patient.middle_name.trim() !== ''
            ) {

                fullName +=
                    ' ' +
                    patient.middle_name;

            }


            selectedBox.innerHTML = '';


            const wrapper =
                document.createElement('div');

            wrapper.className =
                'selected-principal';


            const name =
                document.createElement('span');

            name.textContent =
                fullName;


            const clearButton =
                document.createElement('button');

            clearButton.type =
                'button';

            clearButton.className =
                'clear-principal';

            clearButton.setAttribute(
                'aria-label',
                'Remove principal patient'
            );

            clearButton.innerHTML =
                '&times;';


            clearButton.addEventListener(
                'click',
                function () {

                    clearSelectedPrincipal();

                    searchInput.value = '';

                    searchInput.focus();

                    hideResults();

                }
            );


            wrapper.appendChild(name);

            wrapper.appendChild(clearButton);

            selectedBox.appendChild(wrapper);

        }


        /*
        |--------------------------------------------------------------------------
        | Search patients
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'input',
            function () {

                const searchTerm =
                    searchInput.value.trim();


                clearTimeout(searchTimer);


                /*
                |--------------------------------------------------------------
                | Once the user starts a new search, remove the previous
                | principal selection.
                |--------------------------------------------------------------
                */

                clearSelectedPrincipal();


                if (
                    activeController
                ) {

                    activeController.abort();

                    activeController = null;

                }


                hideResults();


                /*
                |--------------------------------------------------------------
                | Don't search for fewer than 2 characters.
                |--------------------------------------------------------------
                */

                if (
                    searchTerm.length < 2
                ) {

                    return;

                }


                searchTimer =
                    setTimeout(
                        function () {

                            activeController =
                                new AbortController();


                            const url =
                                searchUrl +
                                '?q=' +
                                encodeURIComponent(
                                    searchTerm
                                ) +
                                '&current_id=' +
                                encodeURIComponent(
                                    currentPatientId
                                );


                            fetch(
                                url,
                                {
                                    method: 'GET',
                                    headers: {
                                        'Accept':
                                            'application/json'
                                    },
                                    signal:
                                        activeController.signal
                                }
                            )


                            .then(
                                function (response) {

                                    if (
                                        !response.ok
                                    ) {

                                        throw new Error(
                                            'HTTP error ' +
                                            response.status
                                        );

                                    }


                                    return response.json();

                                }
                            )


                            .then(
                                function (data) {

                                    resultsBox.innerHTML = '';


                                    if (
                                        !data.success
                                    ) {

                                        resultsBox.innerHTML =
                                            '<div class="no-principal-results">' +
                                            'Unable to search patients.' +
                                            '</div>';

                                        resultsBox.style.display =
                                            'block';

                                        return;

                                    }


                                    if (
                                        !data.patients ||
                                        data.patients.length === 0
                                    ) {

                                        resultsBox.innerHTML =
                                            '<div class="no-principal-results">' +
                                            'No patients found.' +
                                            '</div>';

                                        resultsBox.style.display =
                                            'block';

                                        return;

                                    }


                                    let displayedResults =
                                        0;


                                    data.patients.forEach(
                                        function (patient) {

                                            /*
                                            |------------------------------------------------------
                                            | Front-end safety:
                                            | NEVER show the patient currently being edited.
                                            |------------------------------------------------------
                                            */

                                            if (
                                                parseInt(
                                                    patient.id,
                                                    10
                                                ) ===
                                                currentPatientId
                                            ) {

                                                return;

                                            }


                                            let fullName =
                                                patient.surname +
                                                ', ' +
                                                patient.first_name;


                                            if (
                                                patient.middle_name &&
                                                patient.middle_name
                                                    .trim() !== ''
                                            ) {

                                                fullName +=
                                                    ' ' +
                                                    patient.middle_name;

                                            }


                                            const result =
                                                document.createElement(
                                                    'button'
                                                );


                                            result.type =
                                                'button';

                                            result.className =
                                                'principal-result';

                                            result.textContent =
                                                fullName;


                                            result.addEventListener(
                                                'click',
                                                function () {

                                                    /*
                                                    |------------------------------------------------
                                                    | Extra safety check
                                                    |------------------------------------------------
                                                    */

                                                    if (
                                                        parseInt(
                                                            patient.id,
                                                            10
                                                        ) ===
                                                        currentPatientId
                                                    ) {

                                                        return;

                                                    }


                                                    /*
                                                    |------------------------------------------------
                                                    | Store PUBLIC patient ID.
                                                    |------------------------------------------------
                                                    */

                                                    hiddenInput.value =
                                                        patient.patient_id;


                                                    /*
                                                    |------------------------------------------------
                                                    | Display selected patient.
                                                    |------------------------------------------------
                                                    */

                                                    displaySelectedPrincipal(
                                                        patient
                                                    );


                                                    /*
                                                    |------------------------------------------------
                                                    | Clear search box.
                                                    |------------------------------------------------
                                                    */

                                                    searchInput.value =
                                                        '';


                                                    hideResults();

                                                }
                                            );


                                            resultsBox.appendChild(
                                                result
                                            );


                                            displayedResults++;

                                        }
                                    );


                                    if (
                                        displayedResults === 0
                                    ) {

                                        resultsBox.innerHTML =
                                            '<div class="no-principal-results">' +
                                            'No patients found.' +
                                            '</div>';

                                    }


                                    resultsBox.style.display =
                                        'block';

                                }
                            )


                            .catch(
                                function (error) {

                                    if (
                                        error.name ===
                                        'AbortError'
                                    ) {

                                        return;

                                    }


                                    console.error(
                                        'Principal patient search error:',
                                        error
                                    );


                                    resultsBox.innerHTML =
                                        '<div class="no-principal-results">' +
                                        'Unable to search patients.' +
                                        '</div>';

                                    resultsBox.style.display =
                                        'block';

                                }
                            );


                        },
                        250
                    );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Close results when clicking outside
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (event) {

                if (
                    !event.target.closest(
                        '.principal-search'
                    )
                ) {

                    hideResults();

                }

            }
        );


    }
);

/*
|--------------------------------------------------------------------------
| PATIENT PROFILE IMAGE DELETE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const trashIcon =
            document.getElementById(
                "open-patient-delete-tab"
            );


        const deleteTabBtn =
            document.getElementById(
                "patient-delete-tab-btn"
            );


        const deletePanel =
            document.getElementById(
                "patient-pic-delete"
            );


        const confirmBtn =
            document.getElementById(
                "confirm-patient-delete"
            );


        const cancelBtn =
            document.getElementById(
                "cancel-patient-delete"
            );


        const editTabBtn =
            document.querySelector(
                '[data-tab="patient-edit"]'
            );


        /*
        |--------------------------------------------------------------------------
        | If this isn't the patient edit page, stop.
        |--------------------------------------------------------------------------
        */

        if (
            !trashIcon ||
            !deleteTabBtn ||
            !deletePanel ||
            !confirmBtn ||
            !cancelBtn
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Open delete confirmation tab
        |--------------------------------------------------------------------------
        */

        trashIcon.addEventListener(
            "click",
            function () {

                deleteTabBtn.style.display =
                    "inline-block";


                if (
                    typeof activateTab ===
                    "function"
                ) {

                    activateTab(
                        deleteTabBtn
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Cancel
        |--------------------------------------------------------------------------
        */

        cancelBtn.addEventListener(
            "click",
            function () {

                deleteTabBtn.style.display =
                    "none";


                if (
                    typeof activateTab ===
                    "function" &&
                    editTabBtn
                ) {

                    activateTab(
                        editTabBtn
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Confirm deletion
        |--------------------------------------------------------------------------
        */

        confirmBtn.addEventListener(
            "click",
            function () {

                const patientId =
                    trashIcon.dataset.id;

                const imageName =
                    trashIcon.dataset.image;


                if (
                    !patientId ||
                    !imageName
                ) {

                    return;

                }


                confirmBtn.disabled =
                    true;


                fetch(
                    "/futa_hcms/public/modules/ajax/delete_image_ajax.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/x-www-form-urlencoded"
                        },

                        body:
                            "id=" +
                            encodeURIComponent(
                                patientId
                            ) +
                            "&delete_image=" +
                            encodeURIComponent(
                                imageName
                            )
                    }
                )

                .then(
                    function (response) {

                        return response.json();

                    }
                )

                .then(
                    function (data) {

                        if (data.success) {

                            /*
                            |--------------------------------------------------
                            | Change displayed image to generic image.
                            |--------------------------------------------------
                            */

                            const photo =
                                document.getElementById(
                                    "patient-edit-photo"
                                );


                            if (photo) {

                                photo.src =
                                    "/futa_hcms/public/images/patient_pictures/default_profile_pic.png";

                            }


                            const photoName =
                                document.getElementById(
                                    "patient-edit-photo-name"
                                );


                            if (photoName) {

                                photoName.textContent =
                                    "No personal photo uploaded.";

                            }


                            /*
                            |--------------------------------------------------
                            | Remove trash icon.
                            |--------------------------------------------------
                            */

                            trashIcon.remove();


                            /*
                            |--------------------------------------------------
                            | Hide delete tab.
                            |--------------------------------------------------
                            */

                            deleteTabBtn.style.display =
                                "none";


                            /*
                            |--------------------------------------------------
                            | Return to edit tab.
                            |--------------------------------------------------
                            */

                            if (
                                typeof activateTab ===
                                "function" &&
                                editTabBtn
                            ) {

                                activateTab(
                                    editTabBtn
                                );

                            }


                        } else {

                            alert(
                                data.message ||
                                "Unable to delete profile image."
                            );


                            confirmBtn.disabled =
                                false;

                        }

                    }
                )

                .catch(
                    function (error) {

                        console.error(
                            "Patient image delete error:",
                            error
                        );


                        alert(
                            "Unable to delete profile image."
                        );


                        confirmBtn.disabled =
                            false;

                    }
                );

            }
        );

    }
);