$(document).ready(function() {
    let $locale = $('html').attr('lang');
    let $datetimepickerTooltips = {
        en: {
            today: 'Go to today',
            clear: 'Clear selection',
            close: 'Close the picker',
            selectMonth: 'Select Month',
            prevMonth: 'Previous Month',
            nextMonth: 'Next Month',
            selectYear: 'Select Year',
            prevYear: 'Previous Year',
            nextYear: 'Next Year',
            selectDecade: 'Select Decade',
            prevDecade: 'Previous Decade',
            nextDecade: 'Next Decade',
            selectCentury: 'Select Century',
            prevCentury: 'Previous Century',
            nextCentury: 'Next Century',
            incrementHour: 'Increment Hour',
            pickHour: 'Pick Hour',
            decrementHour:'Decrement Hour',
            incrementMinute: 'Increment Minute',
            pickMinute: 'Pick Minute',
            decrementMinute:'Decrement Minute',
            incrementSecond: 'Increment Second',
            pickSecond: 'Pick Second',
            decrementSecond:'Decrement Second',
        },
        sv: {
            today: 'Gå till idag',
            clear: 'Töm alla val',
            close: 'Stäng fältet',
            selectMonth: 'Välj månad',
            prevMonth: 'Föregående månad',
            nextMonth: 'Följande månad',
            selectYear: 'Välj år',
            prevYear: 'Föregående år',
            nextYear: 'Följande år',
            selectDecade: 'Välj decennium',
            prevDecade: 'Föregående decennium',
            nextDecade: 'Följande decennium',
            selectCentury: 'Välj sekel',
            prevCentury: 'Föregående sekel',
            nextCentury: 'Följande sekel',
            incrementHour: 'Öka på timmar',
            pickHour: 'Välj timmar',
            decrementHour:'Minska på timmar',
            incrementMinute: 'Öka på minuter',
            pickMinute: 'Välj minuter',
            decrementMinute:'Minska på minuter',
            incrementSecond: 'Öka sekunder',
            pickSecond: 'Välj sekunder',
            decrementSecond:'Minska på sekunder',
        },
        fi: {
            today: 'Mene tälle päivälle',
            clear: 'Poista valinnat',
            close: 'Sulje kenttä',
            selectMonth: 'Valitse kuukausi',
            prevMonth: 'Edellinen kuukausi',
            nextMonth: 'Seuraava kuukausi',
            selectYear: 'Valitse vuosi',
            prevYear: 'Edellinen vuosi',
            nextYear: 'Seuraava vuosi',
            selectDecade: 'Valitse vuosikymmen',
            prevDecade: 'Edellinen vuosikymmen',
            nextDecade: 'Seuraava vuosikymmen',
            selectCentury: 'Valitse vuosisata',
            prevCentury: 'Edellinen vuosisata',
            nextCentury: 'Seuraava vuosisata',
            incrementHour: 'Lisää tunteja',
            pickHour: 'Valitse tunnit',
            decrementHour:'Vähennä tunteja',
            incrementMinute: 'Lisää minuutteja',
            pickMinute: 'Valitse minuutit',
            decrementMinute:'Vähennä minuutteja',
            incrementSecond: 'Lisää sekunteja',
            pickSecond: 'Valitse sekunnit',
            decrementSecond:'Vähennä sekunteja',
        }
    };
    $('body')
        .on('focus', '.js-datepicker', function(e) {
            $(this).datepicker({
                format: 'dd.mm.yyyy',
                language: $('html').attr('lang'),
                autoclose: true,
                todayBtn: true,
            });
        })

        .on('focus', '.js-datetimepicker', function(e) {
            $(this).datetimepicker({
                widgetParent: $(this).parent(),
                sideBySide: true,
                format: 'DD.MM.YYYY HH:mm',
                locale: $locale,
                showTodayButton: true,
                showClose: true,
                icons: {
                    time: 'text-primary fa fa-clock-o',
                    date: 'text-primary fa fa-calendar',
                    up: 'text-primary fa fa-chevron-up',
                    down: 'text-primary fa fa-chevron-down',
                    previous: 'text-primary fa fa-chevron-left',
                    next: 'text-primary fa fa-chevron-right',
                    today: 'text-primary fa fa-calendar-check-o',
                    clear: 'text-primary fa fa-trash',
                    close: 'text-primary fa fa-close'
                },
                tooltips: $datetimepickerTooltips[$locale]
            });
        })
        ;
});
