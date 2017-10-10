//php bin/console asset:install --symlink
$(document).ready(function() {
        // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.

        var $container = $('#ticketsForm');

        // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
        var index = $container.children().length ;
        console.log('index du billet est ' + index);

        // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
        $('#add_ticket').click(function(e) {
            addTicket($container);

             // évite qu'un # apparaisse dans l'URL
            e.preventDefault();

        });


        // La fonction qui ajoute un formulaire TicketType
        function addTicket($container) {
            // Dans le contenu de l'attribut « data-prototype », on remplace :
            // - le texte "__name__label__" qu'il contient par le label du champ
            // - le texte "__name__" qu'il contient par le numéro du champ
            var template = $container.attr('data-prototype')
                .replace(/__name__label__/g, 'Billet n°' + (index +1))
                .replace(/__name__/g,        index)
            ;

            // On crée un objet jquery qui contient ce template
            var $prototype = $(template);

            var divElt = $('<div></div>');
            divElt.attr('id','ticket-' + index);
            divElt.append('<h3>Billet n° ' + (index+1) + '</h3>');
            divElt.append($prototype);

            // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
            addDeleteLink(divElt);

            // On ajoute le prototype modifié à la fin de la balise <div>
            $container.append(divElt);

            // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
            index++;
        }

        // La fonction qui ajoute un lien de suppression d'une catégorie
        function addDeleteLink(divElt) {
            // Création du lien
            var $deleteLink = $('<a href="#" class="btn btn-danger">Supprimer</a>');

            // Ajout du lien
            divElt.append($deleteLink);

            // Ajout du listener sur le clic du lien pour effectivement supprimer la catégorie
            $deleteLink.click(function(e) {
                divElt.remove();

                e.preventDefault(); // évite qu'un # apparaisse dans l'URL
                return false;
            });
        }
    });
