const tabs = document.querySelectorAll('[role="tab"]');
const panels = document.querySelectorAll('[role="tabpanel"]');
const trashIcon = document.getElementById('open-delete-tab');
const deleteTab = document.getElementById('pic-delete');
const deleteTabBtn = document.getElementById('delete-tab-btn');
const cancelBtn = document.getElementById('cancel-delete');
const confirmBtn = document.getElementById('confirm-delete');
 
const staffEditTab = document.getElementById('staff-edit');
const staffEditBtn = document.querySelector('[data-tab="staff-edit"]');

const trashIcons = document.querySelectorAll('.open-delete-tab');
const staffDeleteTab = document.getElementById('staff-delete');
const staffDeleteBtn = document.querySelector('[data-tab="staff-delete"]');

let deleteMode = null;
let selectedStaffId = null;
let selectedStaffRow = null;
let previousTab = null;


// tabs for the written content within the rectangle on each staff page
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        activateTab(tab);
    });
    tab.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            const index = [...tabs].indexOf(tab);
            const dir = e.key === 'ArrowRight' ? 1 : -1;
            const newTab = tabs[index + dir];
            if(newTab) {
                newTab.focus();
                activateTab(newTab);
                       }
        }
    });
});

function activateTab(tab) {
    tabs.forEach(t => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
    });
    
    panels.forEach(p => {
        p.hidden = true;
        p.classList.remove('active');
    });
    
    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');
    
    const panel = document.getElementById(tab.dataset.tab);
    panel.hidden = false;
    panel.classList.add('active');
}


  if (trashIcon && deleteTabBtn) {
  trashIcon.addEventListener('click', () => {
    
    deleteMode = 'image';
    
    // show delete tab button
    deleteTabBtn.style.display = 'inline-block';

    
    // activate delete tab using existing system
    activateTab(deleteTabBtn);
      
    
    // disable delete icon
    trashIcon.style.pointerEvents = 'none';
  });
}



// for deleting staff accounts in staff/index.php
if (trashIcons.length > 0 && staffDeleteBtn) {
  
trashIcons.forEach(icon => {
  icon.addEventListener('click', () => {

    deleteMode = 'staff';
    selectedStaffId = icon.dataset.id;
    selectedStaffRow = icon.closest('tr');

    const staffName = icon.dataset.name;

    // Capture currently active tab BEFORE switching
   previousTab = document.querySelector('[role="tab"].active');


    const confirmText = document.querySelector('#staff-delete p');
    confirmText.textContent =
      `Are you sure you want to delete ${staffName}'s account?`;

    staffDeleteBtn.style.display = 'inline-block';
    activateTab(staffDeleteBtn);
    
    trashIcons.forEach(icon => {
  icon.style.pointerEvents = 'none';
});
    
  });
});

}

if (cancelBtn) {
  cancelBtn.addEventListener('click', () => {

    if (deleteMode === 'image') {

      deleteTabBtn.style.display = 'none';
      activateTab(staffEditBtn);
      trashIcon.style.pointerEvents = 'auto';

    }

    if (deleteMode === 'staff') {

      staffDeleteBtn.style.display = 'none';

      if (previousTab) {
        activateTab(previousTab);
      }

      trashIcons.forEach(icon => {
        icon.style.pointerEvents = 'auto';
      });

    }

    deleteMode = null;
  });
}

function handleDelete() {

  if (!deleteMode) return;

  const msgBox = document.getElementById('ajax-message');

  /* =========================
     DELETE IMAGE
     ========================= */
  if (deleteMode === 'image') {

    const staffId = trashIcon.dataset.id;
    const imageName = trashIcon.dataset.image;

    fetch('/futa_hcms/public/staff/delete_image_ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(staffId)}&delete_image=${encodeURIComponent(imageName)}`
    })
    .then(res => res.json())
    .then(data => {

      msgBox.textContent = data.message;
      msgBox.hidden = false;

      if (data.success) {
        msgBox.className = 'ajax-message success';
        trashIcon.remove();
        
        // Hide delete tab button
        deleteTabBtn.style.display = 'none';

        // Return to edit tab
        activateTab(staffEditBtn);

        // Reset pointer
        trashIcon.style.pointerEvents = 'auto';
        
        // Reset mode after action
        deleteMode = null;

      } else {
        msgBox.className = 'ajax-message error';
      }

    });

  }

  /* =========================
     DELETE STAFF
     ========================= */
  if (deleteMode === 'staff') {

    fetch('/futa_hcms/public/staff/delete_staff_ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${encodeURIComponent(selectedStaffId)}`
    })
    .then(res => res.json())
    .then(data => {

      msgBox.textContent = data.message;
      msgBox.hidden = false;

      if (data.success) {

        msgBox.className = 'ajax-message success';

        if (selectedStaffRow) {
          selectedStaffRow.remove();
          }
        
         // Hide delete tab button
        staffDeleteBtn.style.display = 'none';

       
        if (previousTab) {
          activateTab(previousTab);
        }
		
		
  //if (previousTab) {
  //  setTimeout(() => {
  //    activateTab(previousTab);
  //  }, 100);
  //}

        trashIcons.forEach(icon => {
        icon.style.pointerEvents = 'auto';
      });


        // Reset state
        selectedStaffId = null;
        selectedStaffRow = null;
        // Reset mode after action
        deleteMode = null;
        
      } else {
        msgBox.className = 'ajax-message error';
      }

    });

  }

  
}


if (confirmBtn) {
confirmBtn.addEventListener('click', handleDelete);
}