/**
 * Trainers Page Module
 */
window.TrainersPage = (function() {
    let config = {};

    function init(options) {
        config = options || {};

        // Handle add trainer form AJAX submission
        const addForm = document.getElementById('addTrainerForm');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(addForm);

                fetch(addForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#addTrainerModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to add trainer.');
                    }
                })
                .catch(() => alert('An error occurred. Please try again.'));
            });
        }
    }

    function toggleFilterSection(header, event) {
        event.preventDefault();
        event.stopPropagation();
        const content = header.nextElementSibling;
        if (content) {
            content.classList.toggle('show');
        }
    }

    function openEditModal(trainer) {
        document.getElementById('editTrainerName').value = trainer.full_name || '';
        document.getElementById('editTrainerSpecialization').value = trainer.specialization || '';
        document.getElementById('editTrainerContact').value = trainer.contact_number || '';
        document.getElementById('editTrainerEmergency').value = trainer.emergency_contact || '';
        document.getElementById('editTrainerAddress').value = trainer.address || '';
        document.getElementById('editTrainerStatus').value = trainer.status || 'active';

        // Format birth_date for input[type=date]
        if (trainer.birth_date) {
            const d = new Date(trainer.birth_date);
            const formatted = d.toISOString().split('T')[0];
            document.getElementById('editTrainerBirthDate').value = formatted;
        } else {
            document.getElementById('editTrainerBirthDate').value = '';
        }

        const form = document.getElementById('editTrainerForm');
        form.action = '/staff-management/trainers/' + trainer.id;

        $('#editTrainerModal').modal('show');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteTrainerName').textContent = name;
        const form = document.getElementById('deleteTrainerForm');
        form.action = '/staff-management/trainers/' + id;
        $('#deleteTrainerModal').modal('show');
    }

    function confirmDelete() {
        document.getElementById('deleteTrainerForm').submit();
    }

    function previewAvatar(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.style.display = 'block';
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    return {
        init: init,
        toggleFilterSection: toggleFilterSection,
        openEditModal: openEditModal,
        openDeleteModal: openDeleteModal,
        confirmDelete: confirmDelete,
        previewAvatar: previewAvatar
    };
})();
