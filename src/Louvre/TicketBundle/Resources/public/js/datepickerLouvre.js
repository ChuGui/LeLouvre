//php bin/console asset:install --symlink
jQuery(function($)
{
    var date = new Date();

    $(".js-datepicker").datepicker(
        {
            beforeShowDay: function(date)
            {
                var day = date.getDay();
                var d = date.getUTCDate();
                var month = date.getUTCMonth();
                if(day == 2 || day == 0 || (d ==30 && month == 3) || (d ==31 && month == 9) || (d ==24 && month == 11))
                {
                    return[false]
                }else{
                    return[true]
                }

            },
            minDate: date,
            altField: "#datepicker",
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            currentText: 'Aujourd\'hui',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.',
            dateFormat: 'dd-mm-yy',
            onClose: function(){
                //Get selected date by client
                var $date = $(this).datepicker('getDate');
                var $day = $date.getDate();
                var $month = $date.getMonth();
                var $year = $date.getFullYear();
                var $fullDate = $day + "-" + ($month+1) + "-" + $year;


                //Remove checkBox Element id=halfdayCheckBox after 14h00 if today's day is selected
                var now = new Date();
                var currentDay = now.getDate();

                var currentMonth = now.getMonth();
                var currentYear= now.getFullYear();
                var currentFullDate= currentDay + "-" + (currentMonth+1) + "-" + currentYear;
                console.log(currentFullDate);
                if(currentFullDate === $fullDate)
                {
                    $("#louvre_ticketbundle_booking_halfday").hide()
                }else{
                    $("#louvre_ticketbundle_booking_halfday").show()
                }
                var americanDate = $year + "-" + ($month+1) + "-" + $day;
                $.ajax({
                    type:'GET',
                    url:'http://localhost/LeLouvre/web/app_dev.php/remainingTicket?date=' + americanDate,
                    dataType: 'JSON',
                    success: function(data)
                    {
                        console.log(data);
                        $('#infoNbTicket').text("Il ne reste plus que " + data + " billet(s) disponible(s) pour le " + $fullDate);
                    }
                })
            }

        }
    );

})