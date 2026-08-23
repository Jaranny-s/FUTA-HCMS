document.addEventListener(
    "DOMContentLoaded",
    function () {


        /*
        |--------------------------------------------------------------------------
        | BASIC INFORMATION ELEMENTS
        |--------------------------------------------------------------------------
        */

        const nationality =
            document.getElementById("nationality");

        const state =
            document.getElementById("state");

        const lga =
            document.getElementById("lga");

        const stateDiv =
            document.getElementById("state-div");

        const lgaDiv =
            document.getElementById("lga-div");

        const dob =
            document.getElementById("date_of_birth");

        const age =
            document.getElementById("age");

        const category =
            document.getElementById("patient_category");


        /*
        |--------------------------------------------------------------------------
        | CATEGORY ELEMENTS
        |--------------------------------------------------------------------------
        |
        | These only exist on university_information.php.
        | Therefore they MUST be checked before using .style.
        |
        */

        const student =
            document.getElementById("student-fields");

        const staff =
            document.getElementById("staff-fields");

        const dependant =
            document.getElementById("dependant-fields");

        const external =
            document.getElementById("external-fields");


        /*
        |--------------------------------------------------------------------------
        | NATIONALITY / STATE / LGA
        |--------------------------------------------------------------------------
        */

        if (
            nationality &&
            state &&
            lga &&
            stateDiv &&
            lgaDiv
        ) {

            const savedLga =
                lga.dataset.selectedLga || "";


            function populateLGAs(
                selectedState,
                selectedLga = ""
            ) {

                lga.innerHTML =
                    '<option value="">Select LGA of Origin</option>';


                if (
                    nigeriaLGAs &&
                    nigeriaLGAs[selectedState]
                ) {

                    nigeriaLGAs[selectedState]
                        .forEach(function (item) {

                            const option =
                                document.createElement(
                                    "option"
                                );

                            option.value =
                                item;

                            option.textContent =
                                item;


                            if (
                                item === selectedLga
                            ) {

                                option.selected =
                                    true;

                            }


                            lga.appendChild(
                                option
                            );

                        });

                }

            }


            function toggleNationality() {

                if (
                    nationality.value ===
                    "Nigeria"
                ) {

                    stateDiv.style.display =
                        "block";

                    lgaDiv.style.display =
                        "block";


                    populateLGAs(
                        state.value,
                        savedLga
                    );

                } else {

                    stateDiv.style.display =
                        "none";

                    lgaDiv.style.display =
                        "none";


                    state.value = "";


                    lga.innerHTML =
                        '<option value="">Select LGA of Origin</option>';

                }

            }


            state.addEventListener(
                "change",
                function () {

                    populateLGAs(
                        this.value
                    );

                }
            );


            nationality.addEventListener(
                "change",
                toggleNationality
            );


            toggleNationality();


            if (savedState) {

                state.value =
                    savedState;

                populateLGAs(
                    savedState,
                    savedLGA
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | DATE OF BIRTH / AGE
        |--------------------------------------------------------------------------
        */

        if (dob && age) {

            function calculateAge() {

                if (!dob.value) {

                    age.value = "";

                    return;

                }


                const birth =
                    new Date(dob.value);

                const today =
                    new Date();


                let years =
                    today.getFullYear() -
                    birth.getFullYear();


                const month =
                    today.getMonth() -
                    birth.getMonth();


                if (
                    month < 0 ||
                    (
                        month === 0 &&
                        today.getDate() <
                        birth.getDate()
                    )
                ) {

                    years--;

                }


                age.value =
                    years;

            }


            dob.addEventListener(
                "change",
                calculateAge
            );


            calculateAge();

        }


        /*
        |--------------------------------------------------------------------------
        | CATEGORY FIELDS
        |--------------------------------------------------------------------------
        |
        | This block will simply do nothing on Basic Information because
        | those elements do not exist there.
        |
        */

        function toggleCategoryFields() {

            /*
            |------------------------------------------------------------------
            | Important safety check
            |------------------------------------------------------------------
            */

            if (
                !category ||
                !student ||
                !staff ||
                !dependant ||
                !external
            ) {

                return;

            }


            student.style.display =
                "none";

            staff.style.display =
                "none";

            dependant.style.display =
                "none";

            external.style.display =
                "none";


            switch (
                category.value
            ) {

                case "Student":

                    student.style.display =
                        "block";

                    break;


                case "Staff":

                    staff.style.display =
                        "block";

                    break;


                case "Dependant":

                    dependant.style.display =
                        "block";

                    break;


                case "External":

                    external.style.display =
                        "block";

                    break;

            }

        }


        if (category) {

            category.addEventListener(
                "change",
                toggleCategoryFields
            );

        }


        toggleCategoryFields();


        /*
        |--------------------------------------------------------------------------
        | REGISTRATION PHOTO DELETE
        |--------------------------------------------------------------------------
        */

        const openDelete =
            document.getElementById(
                "open-registration-delete"
            );

        const deleteConfirm =
            document.getElementById(
                "registration-photo-delete-confirm"
            );

        const confirmDelete =
            document.getElementById(
                "confirm-registration-delete"
            );

        const cancelDelete =
            document.getElementById(
                "cancel-registration-delete"
            );

        const messageBox =
            document.getElementById(
                "registration-photo-message"
            );


        if (
            openDelete &&
            deleteConfirm &&
            confirmDelete &&
            cancelDelete &&
            messageBox
        ) {


            /*
            |------------------------------------------------------------------
            | Open confirmation
            |------------------------------------------------------------------
            */

            openDelete.addEventListener(
                "click",
                function () {

                    deleteConfirm.hidden =
                        false;

                    openDelete.disabled =
                        true;

                }
            );


            /*
            |------------------------------------------------------------------
            | Cancel
            |------------------------------------------------------------------
            */

            cancelDelete.addEventListener(
                "click",
                function () {

                    deleteConfirm.hidden =
                        true;

                    openDelete.disabled =
                        false;

                }
            );


            /*
            |------------------------------------------------------------------
            | Confirm AJAX deletion
            |------------------------------------------------------------------
            */

            confirmDelete.addEventListener(
                "click",
                function () {

                    const imageName =
                        openDelete.dataset.image;


                    if (!imageName) {

                        return;

                    }


                    confirmDelete.disabled =
                        true;


                    fetch(
                        "/futa_hcms/public/modules/ajax/delete_registration_image_ajax.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/x-www-form-urlencoded"
                            },

                            body:
                                "delete_image=" +
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

                            messageBox.textContent =
                                data.message;

                            messageBox.hidden =
                                false;


                            if (data.success) {

                                messageBox.className =
                                    "ajax-message success";


                                /*
                                |--------------------------------------------------
                                | Replace displayed image with generic image.
                                |--------------------------------------------------
                                */

                                const preview =
                                    document.getElementById(
                                        "patient-photo-preview"
                                    );


                                if (preview) {

                                    preview.innerHTML =
                                        '<img src="' +
                                        '/futa_hcms/public/images/patient_pictures/default_profile_pic.png' +
                                        '" alt="Default patient photo" class="patient-profile-image">';

                                }


                                const photoName =
                                    document.getElementById(
                                        "current-photo-name"
                                    );


                                if (photoName) {

                                    photoName.textContent =
                                        "No personal photo uploaded.";

                                }


                                openDelete.remove();

                                deleteConfirm.hidden =
                                    true;


                            } else {

                                messageBox.className =
                                    "ajax-message error";

                                confirmDelete.disabled =
                                    false;

                                openDelete.disabled =
                                    false;

                            }

                        }
                    )

                    .catch(
                        function (error) {

                            console.error(
                                "Patient image deletion error:",
                                error
                            );


                            messageBox.textContent =
                                "Unable to delete the profile photo.";

                            messageBox.className =
                                "ajax-message error";

                            messageBox.hidden =
                                false;


                            confirmDelete.disabled =
                                false;

                            openDelete.disabled =
                                false;

                        }
                    );

                }
            );

        }

    }
);