document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-video-field');
    const container = document.getElementById('video-fields');

    if (addButton && container) {
        addButton.addEventListener('click', function () {
            const prototype = container.dataset.prototype;
            const index = container.querySelectorAll('input').length;
            let newField = prototype.replace(/__name__/g, index);

            // Optional : ajouter placeholder si non défini
            newField = newField.replace(
                /type="text"/,
                'type="text" placeholder="URL de la vidéo (YouTube, Dailymotion…)"'
            );

            container.insertAdjacentHTML('beforeend', newField);
        });
    }
});
