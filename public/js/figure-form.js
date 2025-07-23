document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-video-field');
    const container = document.getElementById('video-fields');

    if (addButton && container) {
        addButton.addEventListener('click', function () {
            // Récupérer le prototype
            const prototype = container.dataset.prototype;

            // Utiliser un index unique basé sur le nombre de champs actuels
            const index = container.querySelectorAll('input').length;

            // Remplacer le placeholder __name__ par l’index
            let newField = prototype.replace(/__name__/g, index);

            // Ajouter un placeholder lisible
            newField = newField.replace(/type="text"/, 'type="text" placeholder="URL de la vidéo (YouTube, Dailymotion…)"');

            // Ajouter le champ au container
            container.insertAdjacentHTML('beforeend', newField);
        });
    }
});

