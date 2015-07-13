@extends('pboapp')


@section('heads')
    <script>
        $(document).ready(function(){
            $.ajax({
                type: "GET",
                url: "data/fixed/googleCalendarData.txt",
                dataType: "text",
                success: function(data) {processData(data);}
            });
        });

        function processData(allText) {
            var record_num = 1;  // or however many elements there are in each row
            var allTextLines = allText.split(/\r\n|\n/);

            for (var i = 0; i < allTextLines.length; i++) {
                var entries = allTextLines[i].split(',');
                var teamName = entries[0];
                var teamCalendarID = entries[1];
                var teamIcal = "https://www.google.com/calendar/ical/"+teamCalendarID+"/public/basic.ics";
                $("#calendarTable").append("<tr>"+"<td>"+teamName+"</td>"+"<td>"+teamCalendarID+"</td>"+"<td><a href='"+teamIcal+"'> "+teamIcal+"</a></td>"+"</tr>");

                console.log(teamName);
            }


        }

    </script>
@endsection

@section('content')

<div class="well">
    <a id="calendarsmartphone"><i class="icon-info-sign"></i></a> Je kan deze publieke agenda&#8217;s importeren op je smartphone/tablet of PC. De competitie kalenders worden automatisch gesynchroniseerd vanop toernooi.nl Altijd en overal meeste recente gegevens op zak!
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Kalender</th>
            <th>code (bv. Android)</th>
            <th>iCal-URL (bv.Apple iOS)</th>
        </tr>
    </thead>
    <tbody id="calendarTable">
    </tbody>
</table>

<div>
    <div class="alignleft"><strong>Android gebruikers (met google account)</strong><br>
        Volg instructies op <a href="https://support.google.com/calendar/answer/37099?hl=nl&amp;ref_topic=1672445">Google : De agenda van een kennis toevoegen.</a><br>
        Synchroniseer nu je google-account op smartphone/tablet (of wees geduldig tot synchronisatie vanzelf loopt.<br>
        Afhankelijk van je gebruikte kalender applicatie (ik gebruik bv. <a href="https://play.google.com/store/apps/details?id=netgenius.bizcal&amp;hl=nl" target="_new">Business calendar</a>) kan je de geïmporteerde kalenders selecteren en bekijken</div>
    <p>&nbsp;</p>
    <div class="alignleft"><strong>Apple iOS (iphone,…) gebruikers met een google account:</strong><br>
        Nog niet uitgeprobeerd maar kijk eens op <a href="https://support.google.com/calendar/answer/151674" target="_new">officiele info van google</a> of op <a href="http://www.howtogeek.com/97566/how-to-sync-your-shared-google-calendars-with-your-iphone/" target="_new">http://www.howtogeek.com/97566/how-to-sync-your-shared-google-calendars-with-your-iphone/</a></div>
    <p>&nbsp;</p>
    <div class="alignleft"><strong>Synchroniseren via iCal (bv. Apple gebruikers zonder google account)</strong><br>
        Elke van bovenstaande agenda’s heeft een iCal-URL op https://www.google.com/calendar/ical/[agenda-code]/public/basic.ics waarbij je [agenda-code] wijzigt door één van bovenstaande. Bv. https://www.google.com/calendar/ical/3j72eskldk70t4pl5jiqv21oa0@group.calendar.google.com/public/basic.ics voor officiële tornooien. <br>Deze iCal-URL kan je bijvoorbeeld gebruiken in Apple iCal. Zie volgende <a href="http://www.googletutor.com/syncing-google-calendar-with-ical" target="_new">screenshots</a></div>

    <!--
    <p>&nbsp;</p>
    <div class="alignleft"><strong>Synchroniseren via CalDAV (bv. Blackberry OS 10 gebruikers)</strong><br>
        Elke van bovenstaande agenda’s heeft een CalDAV-URL op https://www.google.com/calendar/dav/[agenda-code]/events waarbij je [agenda-code] wijzigt door één van bovenstaande. Bv. https://www.google.com/calendar/dav/p4s51i11881rj5k4e198l6l3hk@group.calendar.google.com/events voor Gentse 6G tornooien. <br>Deze CalDAV-URL kan je bijvoorbeeld gebruiken in Blackberry OS 10. Zie volgende <a href="http://www.blackberryos.com/content/friday-tip-add-any-calendar-your-blackberry-10-4880/" target="_new"> handleiding vanaf step3.</a> Indien je over geen google-account beschikt kan je als gebruikersnaam <b>gentsebc.external@gmail.com</b>  en paswoord <b>DUqupre8</b> gebruiken.</div>
    </div>
    -->
</div>
@endsection

@section('printable')
@endsection
