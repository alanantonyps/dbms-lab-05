/* ============================================
   REPORT — Interactions
   Search filter · Edit modal · Delete modal · Toast
   ============================================ */

(function () {
    'use strict';

    /* ============================================================
       SEARCH FILTER
       ============================================================ */
    const input     = document.getElementById('searchInput');
    const clearBtn  = document.getElementById('searchClear');
    const rows      = document.querySelectorAll('#reportTable tbody .data-row');
    const countEl   = document.getElementById('resultCount');

    function updateCount(visible) {
        if (!countEl) return;
        const word = visible === 1 ? 'registration' : 'registrations';
        countEl.innerHTML = 'Showing <strong>' + visible + '</strong> ' + word;
    }

    function filter() {
        const q = (input.value || '').toLowerCase().trim();
        let visible = 0;

        rows.forEach(function (row) {
            const text  = row.textContent.toLowerCase();
            const match = q === '' || text.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        updateCount(visible);
        if (clearBtn) clearBtn.hidden = q === '';
    }

    if (input) {
        input.addEventListener('input', filter);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                input.value = '';
                filter();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            input.focus();
            filter();
        });
    }

    /* ============================================================
       MODAL HELPERS
       ============================================================ */
    function openModal(el) {
        el.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeModal(el) {
        el.hidden = true;
        document.body.classList.remove('modal-open');
    }

    /* ============================================================
       EDIT MODAL
       ============================================================ */
    const editModal  = document.getElementById('editModal');
    const editClose  = document.getElementById('editClose');
    const editCancel = document.getElementById('editCancel');
    const editRegId  = document.getElementById('edit_reg_id');
    const editPart   = document.getElementById('edit_participant');
    const editEvent  = document.getElementById('edit_event');
    const editTeam   = document.getElementById('edit_team');
    const editDate   = document.getElementById('edit_date');

    function openEdit(row) {
        editRegId.value = row.dataset.regId;
        editPart.value  = row.dataset.participantId;
        editEvent.value = row.dataset.eventId;
        editTeam.value  = row.dataset.teamName;
        editDate.value  = row.dataset.regDate;

        openModal(editModal);
        setTimeout(function () { editTeam.focus(); }, 50);
    }

    function closeEdit() {
        closeModal(editModal);
    }

    document.querySelectorAll('.js-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = btn.closest('tr');
            if (row) openEdit(row);
        });
    });

    if (editClose)  editClose.addEventListener('click', closeEdit);
    if (editCancel) editCancel.addEventListener('click', closeEdit);
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeEdit();
        });
    }

    /* ============================================================
       DELETE MODAL
       ============================================================ */
    const deleteModal = document.getElementById('deleteModal');
    const delClose    = document.getElementById('delClose');
    const delCancel   = document.getElementById('delCancel');
    const delRegId    = document.getElementById('del_reg_id');
    const delSummary  = document.getElementById('del_summary');

    function openDelete(btn) {
        delRegId.value = btn.dataset.regId;

        const participant = btn.dataset.participantName || 'this participant';
        const eventName   = btn.dataset.eventName || 'this event';
        delSummary.textContent = participant + ' for ' + eventName;

        openModal(deleteModal);
    }

    function closeDelete() {
        closeModal(deleteModal);
    }

    document.querySelectorAll('.js-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDelete(btn);
        });
    });

    if (delClose)  delClose.addEventListener('click', closeDelete);
    if (delCancel) delCancel.addEventListener('click', closeDelete);
    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal) closeDelete();
        });
    }

    /* ============================================================
       ESC CLOSES ANY OPEN MODAL
       ============================================================ */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (editModal && !editModal.hidden)   closeEdit();
        if (deleteModal && !deleteModal.hidden) closeDelete();
    });

    /* ============================================================
       TOAST AUTO-DISMISS
       ============================================================ */
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(function () {
            toast.classList.add('toast-out');
            setTimeout(function () { toast.remove(); }, 400);
        }, 3500);
    }
})();