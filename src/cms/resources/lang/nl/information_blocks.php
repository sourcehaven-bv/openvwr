<?php

declare(strict_types=1);

return [
    'avg_responsible_processing_record' => [
        'step_processing_name_title' => 'Informatie over Naam Verwerking',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Vul hier de naam van de gegevensverwerking in zoals bekend binnen jouw organisatie, zoals  "subsidieadministratie" of "nieuwsbrieven sturen".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Leg verwerkingen vast</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Alle handelingen met persoonsgegevens, zoals opslaan, doorgeven of vernietigen, gelden als &quot;verwerking&quot;. Registreer deze in het AVG Verantwoordelijke register. Categoriseer activiteiten zoals &quot;subsidieadministratie&quot;, &quot;nieuwsbrieven sturen&quot; of &quot;portaal bieden&quot; (inclusief verzamelen, opslaan, delen en vernietigen van gegevens).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerking</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">AVG definieert gegevensverwerking als &quot;bewerking of reeks bewerkingen met betrekking tot persoonsgegevens, al dan niet geautomatiseerd.&quot; Dit omvat verzamelen, vastleggen, ordenen, opslaan, bijwerken, opvragen, raadplegen, gebruiken, verstrekken, verspreiden, beschikbaar stellen, combineren, afschermen, wissen of vernietigen van gegevens (art. 4(2) AVG).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verwerkingen die niet hoeven te worden vastgelegd</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Volledig handmatige verwerkingen zonder dossier (bijv. handgeschreven notities) hoeven niet geregistreerd. Hetzelfde geldt voor persoonlijke/huishoudelijke activiteiten (bijv. sociale media contacten buiten werktijd). Deze vallen buiten de AVG-scope. Verwerkingen als verwerker moeten worden vastgelegd in het &quot;AVG Verwerker&quot; register.</p>',

        'step_responsible_title' => 'Informatie over Verwerkingsverantwoordelijke',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG is de verwerkingsverantwoordelijke: &ldquo;een natuurlijk persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat, alleen of samen met anderen, het doel van en de middelen voor de verwerking van persoonsgegevens vaststelt&rdquo; (art. 4 (7) AVG).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Kies de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Zoek de verwerkinsverantwoordelijke door te beginnen met typen. Indien de verwerkinsverantwoordelijke nog niet in het systeem zit, druk op het &#39;+&#39; teken en voer de gegevens van de verwerkingsverantwoordelijke in. Vul de functie in en mogelijk contactdetails.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voeg meerdere verwerkingsverantwoordelijken toe indien nodig, bijv. voor samenwerkingsverbanden. </p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Vul alleen verwerkingsverantwoordelijken in waarvoor u persoonsgegevens verwerkt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een partij kan verwerkingsverantwoordelijke zijn, doordat:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Feitelijke omstandigheden (gedrag) hiertoe leiden. Hulpvragen zijn in dat geval: Waarom vindt deze verwerking plaats? Wie heeft dit ge&iuml;nitieerd? Wie bepaalt de bewaartermijnen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Dit zo is vastgelegd in de wet, een uitspraak van een toezichthouder (zoals de Autoriteit Persoonsgegevens) of een contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Feitelijke omstandigheden (Gedrag) zijn belangrijker dan een contract waarin is bepaald wie verwerkingsverantwoordelijke is.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Onderlinge verdeling verantwoordelijkheid</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bij meer dan &eacute;&eacute;n verwerkingsverantwoordelijke, vul &eacute;&eacute;nmaal de onderlinge verdeling in.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Als verwerker heeft u hier geen invloed op en bent u niet verplicht dit in het register op te nemen. Voeg het toe als beschikbaar.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Qua verantwoordelijkheidsverdeling, zijn de volgende varianten mogelijk:</p>
            <ul class="list-disc list-outside ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Gezamenlijke verwerkingsverantwoordelijkheid</span>:<br>Meerdere partijen stellen samen doel en middelen vast. Verplichte onderlinge regeling (artikel 26 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Zelfstandige verwerkingsverantwoordelijkheid</span>:<br>Hiervan is sprake als partijen samenwerken, maar zelf afzonderlijk doel en middelen bepalen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Combinatie van beide</span>:<br>Het kan zijn dat er bij &eacute;&eacute;n verwerking, zowel gezamenlijke als zelfstandige verantwoordelijkheid is.</li>
            </ul>',

        'step_processor_title' => 'Informatie over Verwerker',
        'step_processor_info' => '
            <p class="text-sm text-gray-500 mb-4">Een verwerker is iemand of een organisatie die door een verwerkingsverantwoordelijke is ingeschakeld om namens de verwerkingsverantwoordelijke persoonsgegevens te verwerken. Volgens de AVG is een verwerker: &ldquo;een natuurlijke persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat ten behoeve van de verwerkingsverantwoordelijke persoonsgegevens verwerkt&rdquo; (artikel 4(8) AVG). </p>
            <p class="text-sm text-gray-500">Een voorbeeld hiervan kan zijn &quot;Dienst ICT Uitvoering (DICTU) van Min EZK&quot; of de Cloud leverantier.</p>',

        'step_receiver_title' => 'Informatie over Ontvangers',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">De AVG verstaat onder de ontvanger: &ldquo;een natuurlijke persoon of een rechtspersoon, een overheidsinstantie, een dienst of ander orgaan, al dan niet een derde, aan wie/waaraan persoonsgegevens worden verstrekt. Overheidsinstanties die mogelijk persoonsgegevens ontvangen in het kader van een bijzonder onderzoek overeenkomstig het Unierecht of het lidstatelijke recht gelden echter niet als ontvangers [&hellip;]&rdquo; (art. 4(9) AVG).</p>',
        'step_receiver_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de ontvangers in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">U dient hier de personen, afdelingen, organisaties of instanties waaraan de persoonsgegevens worden verstrekt, te vermelden. Voor ontvangers buiten uw organisatie, kunt u de betreffende organisatie benoemen evenals het betreffende onderdeel/de functiegroep, wanneer dat bij u bekend is. Indien mogelijk, is verder het advies om aan te geven waarom de betreffende ontvangers de persoonsgegevens ontvangen en eventueel welke gegevens het betreft.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> neem geen namen van individuele medewerkers op, het betreft enkel categorie&euml;n.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de ontvangers</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De ontvanger kan een organisatie (rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan), afdeling of een natuurlijke persoon zijn aan wie u persoonsgegevens verstrekt. De ontvanger kan zich daarnaast zowel binnen de organisatie van de verwerkingsverantwoordelijke bevinden, als erbuiten. Wanneer een ontvanger zich buiten de organisatie van de verwerkingsverantwoordelijke bevindt, kan deze de privacy positie hebben van de verwerker, derde of de betrokkene.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Voorbeelden van ontvangers</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Zoals hierboven reeds omschreven kunnen ontvangers zich zowel binnen als buiten de organisatie van de verwerkingsverantwoordelijke bevinden. Zie hieronder een aantal voorbeelden van ontvangers binnen en buiten de organisatie van de verwerkingsverantwoordelijke:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Afdelingen, teams of medewerkers die belast zijn met de verwerking van persoonsgegevens voor de uitvoering van hun taken of werkzaamheden, zoals:
                    <ul class="list-disc list-outside mb-4 ml-5">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Medewerkers van de communicatieafdeling die toegang hebben tot klantgegevens;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Medewerkers van de P&amp;O-afdeling die toegang hebben tot de personeelsdossiers;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Beveiligingsmedewerkers die toegang hebben tot de beelden van de beveiligingscamera&#39;s;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Functioneel beheerders die hiertoe toegang hebben tot de persoonsgegevens binnen een applicatie/systeem;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Leidinggevenden die structureel toegang hebben tot bijvoorbeeld persoonsgegevens in rapportages.</li>
                    </ul>
                </li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">(Sub)Verwerkers: De (sub)verwerkers die zijn opgenomen onder de tab &lsquo;Verwerker&rsquo; in dit AVG-register dienen hier te worden &lsquo;herhaald&rsquo;.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Betrokkenen: De natuurlijke personen wiens persoonsgegevens worden verwerkt (zie ook art. 4(1) AVG). Aan hen zouden hun eigen gegevens ook structureel kunnen worden verstrekt. Denk aan medewerkers die hun salarisstrook ontvangen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Derden: De personen/partijen aan wie persoonsgegevens worden verstrekt en die niet zijn aan te merken als de verwerkingsverantwoordelijke, de verwerker, de betrokkene of de personen die onder het rechtstreekse gezag van de verwerkingsverantwoordelijke of verwerker gemachtigd zijn om persoonsgegevens te verwerken (zie ook art. 4(10) AVG). In tegenstelling tot de verwerker zal de &lsquo;derde&rsquo; de gegevens veelal voor diens eigen behoefte verwerken. Derden zijn bijvoorbeeld:
                    <ul class="list-disc list-outside mb-4">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Concernonderdelen waarbinnen de doorgifte van persoonsgegevens plaatsvindt (zoals de doorgifte van personeelsgegevens van de dochtermaatschappij naar de moedermaatschappij);</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Verschillende Rijksonderdelen waarbinnen doorgifte van persoonsgegevens plaatsvindt;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300">Verzekeringsmaatschappijen, arbodiensten, incassobureaus etc.</li>
                    </ul>
                </li>
            </ul>',

        'step_processing_goal_title' => 'Informatie over Doel & Grondslag',
        'step_processing_goal_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG moeten persoonsgegevens alleen worden verzameld voor specifieke, expliciete en gerechtvaardigde doeleinden (art. 5(1)(b) AVG). Ook is een wettelijke basis nodig voor de verwerking (art. 6(1) AVG).</p>',
        'step_processing_goal_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over het doel in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan voor welke doeleinden u persoonsgegevens verwerkt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over het doel</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Doelen kunnen een deel van het proces betreffen, zoals het beoordelen van subsidieaanvragen. Dit betekent dat er doorgaans meerdere doeleinden van een verwerking zijn, of de doelomschrijving uit meerdere onderdelen bestaat.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de rechtsgrond</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Persoonsgegevens mogen alleen worden verwerkt als er een rechtsgrond is. Hier zijn de mogelijke rechtsgronden (art. 6 AVG):</p>
            <ol class="list-decimal list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Toestemming van de betrokkene (intrekbaar)</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitvoering of voorbereiding van een overeenkomst</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Voldoen aan een wettelijke verplichting</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Bescherming van vitale belangen</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitoefening van openbaar gezag of algemeen belang</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Gerechtvaardigde belangen van de verwerkingsverantwoordelijke of een derde</li>
            </ol>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Uitzondering op de rechtsgrond &lsquo;gerechtvaardigd belang&rsquo; voor de overheid</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Overheidsinstanties mogen het gerechtvaardigd belang niet als basis gebruiken voor gegevensverwerking binnen hun overheidstaken. Ze moeten een andere rechtsgrond hebben, meestal taak van algemeen belang.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">Voor &quot;bedrijfsmatige handelingen&quot;, zoals salarisadministratie, kunnen ze wel het gerechtvaardigd belang gebruiken.</p>',

        'step_stakeholder_data_title' => 'Informatie over Betrokkenen en gegevens',
        'step_stakeholder_data_info' => '
            <p class="text-sm text-gray-500">In de AVG wordt de persoon over wie informatie wordt verwerkt aangeduid als &quot;betrokkene&quot; (art. 4(1) AVG). Persoonsgegevens zijn de gegevens waarmee de betrokkene direct of indirect kan worden ge&iuml;dentificeerd.</p>',
        'step_stakeholder_data_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de betrokkenen en de gegevens in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan welke categorie&euml;n betrokkenen en persoonsgegevens u verwerkt. Duid de categorie&euml;n betrokkenen aan (zoals werknemers, sollicitanten) en geef aan welke persoonsgegevens u verwerkt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Per categorie betrokkenen specificeert u welke gegevens u verwerkt en waarom. Voeg doelen toe die u eerder in &quot;Doel &amp; Grondsldag&quot; hebt ingevuld.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over persoonsgegevens</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De AVG is all&eacute;&eacute;n van toepassing op persoonsgegevens. Persoonsgegevens zijn alle gegevens die:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Betreffen een persoon;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Identificeren een persoon direct;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Maken een persoon identificeerbaar.</li>
            </ul>
            <ul class="list-outside mb-4">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 1) De gegevens moeten over de persoon gaan.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 2) Gegevens identificeren een persoon als ze uniek zijn.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 3) Een persoon is identificeerbaar als zijn identiteit redelijkerwijs kan worden vastgesteld.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 4) De AVG is alleen van toepassing op gegevens over natuurlijke personen, niet op rechtspersonen, dieren of zaken.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Meer informatie over persoonsgegevens en de betrokkene? Zie o.a. de website van de Autoriteit Persoonsgegevens over dit onderwerp.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over het &quot;Verzameldoel&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Omschrijf het doel waarvoor de gegevens oorspronkelijk zijn verzameld. Dit kan verschillen van het doel van de uiteindelijke verwerking.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Bewaartermijn&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bewaar persoonsgegevens niet langer dan nodig. Respecteer wettelijke bewaartermijnen. Voor overheidsverwerkingen geldt de Archiefwet 1995. Raadpleeg de toepasselijke selectielijst voor bewaartermijnen.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Bron&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Houd bij waar de persoonsgegevens vandaan komen. Dit kan rechtstreeks van de betrokkene zijn, maar ook van andere bronnen. Duid de herkomst helder aan.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Verplichte levering&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef duidelijk aan of de betrokkene verplicht is gegevens te verstrekken en de mogelijke gevolgen van niet-verstrekking.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over bijzondere persoonsgegevens, strafrechtelijke gegevens en BSN</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bijzondere persoonsgegevens zijn gegevens over ras, etniciteit, politieke voorkeur, religie, seksualiteit, lidmaatschap van een vakbond, genetische, biometrische, en gezondheidsgegevens.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Verwerking is in principe verboden, tenzij uitzonderingen gelden volgens de AVG (art. 9 AVG) en UAVG.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voorbeelden waarop de verwerking is toegestaan, zijn:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De betrokkene heeft hiervoor uitdrukkelijke toestemming gegeven;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor arbeidsrechtelijke zaken of zaken mb.t. sociale zekerheid;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De betrokkene heeft de gegevens voor dit doeleinde kennelijk openbaar gemaakt;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk in het kader van een gerechtelijke procedure;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De is noodzakelijk om aan een volkenrechtelijke verplichting te voldoen;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor arbeidsgeneeskunde;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor de volksgezondheid.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Vermeld de uitzondering die van toepassing is op het verwerken van bijzondere persoonsgegevens.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Let op:</span> Voor het verwerken van strafrechtelijke gegevens en Burgerservicenummers gelden strikte voorwaarden: zie artikel 10 AVG en 46 UAVG. Vermeld of voldaan wordt aan de voorwaarden. </p>',

        'step_decision_making_title' => 'Informatie over Besluitvorming',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">Conform de AVG heeft de betrokkene &ldquo;het recht niet te worden onderworpen aan een uitsluitend op geautomatiseerde verwerking, waaronder profilering, gebaseerd besluit waaraan voor hem rechtsgevolgen zijn verbonden of dat hem anderszins in aanmerkelijke make treft&rdquo; (art. 22(1) AVG). Er is sprake van geautomatiseerde besluitvorming wanneer persoonsgegevens worden gebruikt om tot een bepaalde beslissing over de betrokkene te komen, en deze beslissing genomen wordt zonder noemenswaardige inbreng van een mens.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Geautomatiseerde besluitvorming</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In dit onderdeel dient u aan te geven of er wel/geen sprake is van geautomatiseerde besluitvorming. Er is sprake van geautomatiseerde besluitvorming wanneer u of uw organisatie besluiten neemt over betrokkenen (bijvoorbeeld wel/geen recht op een vergunning), op geautomatiseerde wijze (de computer maakt de beslissing, niet een mens), en waaraan rechtsgevolgen zijn verbonden, of die hen anderszins in aanmerkelijke mate treffen, zonder enige menselijke tussenkomst.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Indien er sprake is van geautomatiseerde besluitvorming, dient u &lsquo;Ja&rsquo; aan te klikken. Vervolgens dient u informatie in te vullen over:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De achterliggende logica (u kunt hierbij denken aan informatie over hoe de gegevens worden gebruikt om tot het besluit te komen, welke wiskundige en statistische procedures worden gevolgd, welke beoordelings- of selectieregels worden toegepast om te resulteren in het uiteindelijke besluit);</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Het belang en de verwachte gevolgen van de verwerking voor de betrokkenen (bijvoorbeeld wel/geen toekenning van een vergunning).</li>
            </ul>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verbod</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Het is in beginsel verboden om betrokkenen te onderwerpen aan geautomatiseerde besluitvorming, waaronder profilering, wanneer dit rechtsgevolgen heeft voor de betrokkene, of dit de betrokkene anderszins in aanzienlijke mate treft. Denk aan besluiten die door de computer worden genomen waardoor een arbeidscontract wordt opgezegd, een leverancier geen overeenkomst mag aangaan met een overheidsdienst, of iemand een sollicitatiegesprek wordt ontzegd. Een voorbeeld van profilering die betrokkenen in aanzienlijke mate treft, is het opstellen van een kredietwaardigheidsprofiel, op basis waarvan vervolgens wordt besloten of aan iemand wel/geen krediet wordt verstrekt.</p>',

        'step_system_title' => 'Informatie over Applicaties en Systemen',
        'step_system_info' => '
            <p class="text-sm text-gray-500">Vanuit het oogpunt van beveiliging van de persoonsgegevens en om het eventueel herstel daarvan in geval van (bijvoorbeeld) een datalek te optimaliseren, is het van belang om te weten met welk informatiesysteem of applicatie de verwerking plaatsvindt. U dient zich hierbij te realiseren dat een informatiesysteem of applicatie niet hetzelfde is als een verwerking van persoonsgegevens.</p>',
        'step_system_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul namen van informatiesystemen/applicaties in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Zoek het systeem / de applicatie door te beginnen met typen. Selecteer de applicatie uit de lijst. Indien het systeem nog niet is ingevoerd: voer hier de naam in van het(/de) informatiesysteem(en) of de applicatie(s) door middel waarvan de verwerking plaatsvindt. Let a.u.b. op de juiste spelling. Dit in verband met de zoekfunctie in dit register.</p>',

        'step_security_title' => 'Informatie over Beveiliging',
        'step_security_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG moeten de verwerker, (sub)verwerker en de verwerkingsverantwoordelijke passende technische en organisatorische maatregelen nemen (artikel 32 AVG).</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Toelichting maatregelen</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Beschrijf hier, indien relevant, kort de technische en organisatorische beveiligingsmaatregelen voor persoonsgegevens, zoals encryptie, wachtwoorden, versleutelde verbindingen, autorisatiematrix, beleid en fysieke toegangscontrole. Deze beschrijving hoeft niet gedetailleerd te zijn.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> In de verwerkersovereenkomst worden vaak specifieke afspraken over beveiligingsmaatregelen gemaakt. Je kunt deze overnemen in het register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonimiseren</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Indien er sprake is van het pseudonimiseren van de gegevens, vul hier in welke gegevens gepseudonimiseerd worden en welke methodieken hiervoor worden toegepast.</p>',

        'step_passthrough_title' => 'Informatie over Doorgifte',
        'step_passthrough_info' => '
            <p class="text-sm text-gray-500 mb-4">Niet elk land beschermt persoonsgegevens op dezelfde manier. Binnen de Europese Economische Ruimte (EER) zijn de regels vergelijkbaar, waardoor persoonsgegevens hier vrij kunnen worden doorgegeven. Buiten de EER is dit anders. Doorgifte naar landen buiten de EER is alleen toegestaan als er passende waarborgen zijn (zie ook hoofdstuk V AVG).</p>
            <p class="text-sm text-gray-500 mb-4">De EER omvat alle EU-landen plus Noorwegen, Liechtenstein en IJsland. Deze drie landen hebben een vergelijkbaar niveau van gegevensbescherming. Persoonsgegevens uit Nederland mogen zonder extra waarborgen worden doorgegeven binnen de EER.</p>
            <p class="text-sm text-gray-500">Overigens kan hier ook het Rijksbeleid inzake cloud van toepassing zijn, indien de gegevens in de cloud van een derde land worden verwerkt (opgeslagen).</p>',
        'step_passthrough_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de doorgifte in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan of je persoonsgegevens doorgeeft aan landen buiten de EER of aan internationale organisaties. Indien het geval, geef dan aan naar welke landen je gegevens doorgeeft. Controleer op de website van de Europese Commissie of het betreffende land een passend beschermingsniveau heeft.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">De passende waarborgen</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De volgende waarborgen bij doorgifte aan een derde land of internationale (volkenrechtelijke) organisatie (denk aan de EU-instituties, de Verenigde Naties, etc) kunnen worden getroffen:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Adequaatheidsbesluit door de Europese Commissie waarbij zij heeft besloten dat deze landen een passend beschermingsniveau waarborgen (art. 45 AVG). <span class="font-bold">Let op:</span> adequaatheidsbesluiten kunnen per direct worden ingetrokken door bijvoorbeeld het Hof van Justitie van de Europese Unie</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Juridisch bindend en afdwingbaar besluit tussen overheidsinstanties of -organen (art. 46(2)(a) en 46(3)(b) AVG en overweging 108 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Bindende bedrijfsvoorschriften (binding corporate rules) binnen een concern of groep van ondernemingen (art. 46(3)(b) en 47 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Partijen zijn de standaardbepalingen van de Europese Commissie (standard contractual clauses) inzake gegevensbescherming overeengekomen (art. 46(2)(c) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Partijen zijn de standaardbepalingen van de toezichthouder (voor Nederland: de Autoriteit Persoonsgegevens) overeengekomen (art. 46(2)(d) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is een goedgekeurde gedragscode van toepassing (art. 40 en 46(2)(e) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is sprake van een rechtelijke uitspraak of besluit van een bestuursorgaan gebaseerd op een verdrag (art. 48 AVG.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De toezichthouder (voor Nederland: de Autoriteit Persoonsgegevens) heeft toestemming gegeven voor de doorgifte op basis van contractsbepalingen tussen partijen of bepalingen in administratieve regelingen tussen overheidsinstanties of -organen (art. 46(3) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is sprake van een rechtelijke uitspraak of besluit van een administratieve autoriteit welke is gebaseerd op een internationale overeenkomst (art. 48 AVG.</li>
            </ul>',

        'step_geb_dpia_title' => 'Informatie over GEB (DPIA)',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">Een GEB of Data Protection Impact Assessment (DPIA) is een voorafgaande toetsing aan een verwerking die minimaal aan artikel 35 AVG moet voldoen, en na advies van de Functionaris Gegevensbescherming (FG) op managementniveau moet zijn vastgesteld. Voor het register van verwerkingsactiviteiten voert het te ver om inhoud en proces verdergaand uit te leggen.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Hoog Risico? Dan een GEB (DPIA)!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerkingsverantwoordelijke is verplicht om een GEB (DPIA) uit te voeren voor verwerkingen die een hoog risico met zich meebrengen voor de betrokkenen. Denk bijvoorbeeld aan een nieuwe verwerking of een reeds bestaande verwerking met een gewijzigd risico met bijzondere persoonsgegevens (zoals gezondheidsgegevens).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">U kunt hier aangeven of er een GEB (DPIA) is uitgevoerd t.a.v. de verwerkingen die door de organisatie worden verricht. Zo ja, dan kunt u deze GEB (DPIA) (indien beschikbaar) ook toevoegen aan deze verwerking onder de tab &#39;Documenten &amp; Bijlagen&rsquo;.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Raadpleeg onderstaande referenties voor een nadere uitleg van &quot;hoog risico&quot; en de criteria of een GEB (DPIA) voor een verwerkingsverantwoordelijke verplicht is.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Staatscourant van het Koninkrijk der Nederlanden, Besluit inzake lijst van verwerkingen van persoonsgegevens waarvoor een gegevensbeschermingseffectbeoordeling ( GEB (DPIA)) verplicht is.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitleg over de 9 criteria uit de WP248 artikel 29 richtsnoeren 9 criteria voor verwerkingen waarvoor een GEB (DPIA) moet worden uitgevoerd (pagina 10 tot en met 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Informatie van de AP over de voorwaarden en het uitvoeren van PIA&rsquo;s.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Maak voor het beantwoorden van de vragen zonodig gebruik van intern ondersteuningsmateriaal binnen uw organisatie (indien aanwezig), of raadpleeg de Functionaris voor gegevensbescherming of de Privacy Officer (indien aanwezig).</li>
            </ul>',

        'step_contact_person_title' => 'Informatie over Contactpersoon',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">Bij een verwerking moet worden aangegeven wie voor deze verwerking de contactpersoon is. De gegevens van de contactpersoon zijn voor intern gebruik en worden niet op de openbare website gepubliceerd.</p>',

        'step_attachments_title' => 'Informatie over Bijlagen',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">Als u documenten toevoegt, mag het ook om conceptversies gaan. Geef duidelijk aan wat voor soort document het is en of het concept of definitief is. Toevoegen, wijzigen of verwijderen van documenten vereist geen nieuwe versie van de verwerking. Voeg opmerkingen toe voor verduidelijking.</p>',

        'step_remarks_title' => 'Informatie over Opmerkingen',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500" mb-4>Voeg opmerkingen toe aan de verwerking. Deze zijn intern en worden niet openbaar gemaakt.</p>
            <p class="text-sm text-gray-500">Het kunnen notities zijn over de betrokken medewerker, herinneringen, mededelingen, openstaande zaken, mutatiedata, verwijzingen naar andere verwerkingen of documenten zoals PIA&#39;s, verwerkersovereenkomsten, vastgestelde regelingen bij meerdere verwerkingsverantwoordelijken, beheermaatregelen op basis van een GEB/PIA, of datalekken met het informatiesysteem, enz.</p>',
        'step_remarks_extra_info' => '',

        'step_publish_title' => 'Publiceren',
        'step_publish_info' => '
            <p class="text-sm text-gray-500 mb-4">Geef hier aan vanaf welke datum deze verwerking op de publieke website getoond mag worden.</p>
            <p class="text-sm text-gray-500"><span class="font-bold">Let op:</span> Indien u dit veld leeg laat zal de verwerking op geen enkel moment gepubliceerd worden naar de publieke website.</p>',
    ],

    'avg_processor_processing_record' => [
        'step_processing_name_title' => 'Informatie over Naam Verwerking',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Vul hier de naam van de gegevensverwerking in zoals bekend binnen jouw organisatie, zoals  "subsidieadministratie" of "nieuwsbrieven sturen".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Leg verwerkingen vast</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Alle handelingen met persoonsgegevens, zoals opslaan, doorgeven of vernietigen, gelden als &quot;verwerking&quot;. Registreer deze in het AVG Verwerker register. Categoriseer activiteiten zoals &quot;subsidieadministratie&quot;, &quot;nieuwsbrieven sturen&quot; of &quot;portaal bieden&quot; (inclusief verzamelen, opslaan, delen en vernietigen van gegevens).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerking</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">AVG definieert gegevensverwerking als &quot;bewerking of reeks bewerkingen met betrekking tot persoonsgegevens, al dan niet geautomatiseerd.&quot; Dit omvat verzamelen, vastleggen, ordenen, opslaan, bijwerken, opvragen, raadplegen, gebruiken, verstrekken, verspreiden, beschikbaar stellen, combineren, afschermen, wissen of vernietigen van gegevens (art. 4(2) AVG).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verwerkingen die niet hoeven te worden vastgelegd</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Volledig handmatige verwerkingen zonder dossier (bijv. handgeschreven notities) hoeven niet geregistreerd. Hetzelfde geldt voor persoonlijke/huishoudelijke activiteiten (bijv. sociale media contacten buiten werktijd). Deze vallen buiten de AVG-scope. Verwerkingen als verwerker moeten worden vastgelegd in het &quot;AVG Verwerker&quot; register.</p>',

        'step_responsible_title' => 'Informatie over Verwerkingsverantwoordelijke',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG is de verwerkingsverantwoordelijke: &ldquo;een natuurlijk persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat, alleen of samen met anderen, het doel van en de middelen voor de verwerking van persoonsgegevens vaststelt&rdquo; (art. 4 (7) AVG).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Kies de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Zoek de verwerkinsverantwoordelijke door te beginnen met typen. Indien de verwerkinsverantwoordelijke nog niet in het systeem zit, druk op het &#39;+&#39; teken en voer de gegevens van de verwerkingsverantwoordelijke in. Vul de functie in en mogelijk contactdetails.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voeg meerdere verwerkingsverantwoordelijken toe indien nodig, bijv. voor samenwerkingsverbanden. </p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Vul alleen verwerkingsverantwoordelijken in waarvoor u persoonsgegevens verwerkt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een partij kan verwerkingsverantwoordelijke zijn, doordat:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Feitelijke omstandigheden (gedrag) hiertoe leiden. Hulpvragen zijn in dat geval: Waarom vindt deze verwerking plaats? Wie heeft dit ge&iuml;nitieerd? Wie bepaalt de bewaartermijnen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Dit zo is vastgelegd in de wet, een uitspraak van een toezichthouder (zoals de Autoriteit Persoonsgegevens) of een contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Feitelijke omstandigheden (Gedrag) zijn belangrijker dan een contract waarin is bepaald wie verwerkingsverantwoordelijke is.</p>',

        'step_processor_title' => 'Informatie over Subverwerker',
        'step_processor_info' => '
            <p class="text-sm text-gray-500">Een subverwerker is iemand die door een andere verwerker is ingeschakeld om namens de verwerkingsverantwoordelijke persoonsgegevens te verwerken. Volgens de AVG is een verwerker: “een natuurlijke persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat ten behoeve van de verwerkingsverantwoordelijke persoonsgegevens verwerkt” (artikel 4(8) AVG).</p>',
        'step_processor_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de subverwerker in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef hier aan of je gebruik maakt van subverwerkers. Zo ja, vul de benodigde informatie over de subverwerkers in.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over subverwerker</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een subverwerker kan een rechtspersoon, overheidsinstantie, dienst of ander orgaan zijn, of zelfs een natuurlijke persoon. Overheidsinstanties, diensten of andere organen kunnen bijvoorbeeld bestuursorganen zijn, zoals ministers en colleges van burgemeester & wethouders, zelfstandige bestuursorganen (ZBO’s) of gemeenschappelijke regelingen.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De subverwerker voert verwerkingen uit in opdracht van de verwerker ten behoeve van de verwerkingsverantwoordelijke, en staat niet onder direct gezag van de verwerker. Om te bepalen of iemand een subverwerker is, kijk je naar de specifieke activiteiten die de ingeschakelde partij in een bepaalde context uitvoert.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een subverwerker voert enkel uitvoerende taken uit en heeft geen zeggenschap over het doel van de gegevensverwerking. De subverwerker volgt de instructies van de verwerker, die op zijn beurt de instructies van de verwerkingsverantwoordelijke opvolgt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Daarnaast valt een subverwerker niet onder het directe gezag van een verwerker, wat betekent dat de subverwerker geen onderdeel is van de juridische entiteit van de verwerker. Bijvoorbeeld, een medewerker van de verwerker is geen subverwerker omdat deze onder direct gezag valt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> In principe bepaalt de verwerkingsverantwoordelijke het doel en de (essentiële kenmerken van de) middelen van de verwerking. De verwerker voert enkel uit. In de praktijk kiezen verwerkers wel vaak zelf de (technische) middelen, maar zolang subverwerkers niet het doel bepalen of bijvoorbeeld niet bepalen welke gegevens ze verzamelen, blijven ze subverwerkers.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Subverwerkersovereenkomst</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Als verwerker heb je toestemming van de verwerkingsverantwoordelijke nodig (artikel 28(2) AVG) om subverwerkers in te schakelen. Deze toestemming wordt meestal gegeven via de verwerkersovereenkomst tussen de verwerkingsverantwoordelijke en de verwerker.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Daarnaast moet de verwerker met elke subverwerker een aparte subverwerkersovereenkomst sluiten. Hierin worden dezelfde verplichtingen opgelegd aan de subverwerker als in de verwerkersovereenkomst tussen de verwerkingsverantwoordelijke en de verwerker.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> De subverwerkersovereenkomst kunt u opslaan onder de tab "Documenten & Bijlagen" in dit register.</p>',

        'step_receiver_title' => 'Informatie over Ontvanger',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">De AVG verstaat onder de ontvanger: &ldquo;een natuurlijke persoon of een rechtspersoon, een overheidsinstantie, een dienst of ander orgaan, al dan niet een derde, aan wie/waaraan persoonsgegevens worden verstrekt. Overheidsinstanties die mogelijk persoonsgegevens ontvangen in het kader van een bijzonder onderzoek overeenkomstig het Unierecht of het lidstatelijke recht gelden echter niet als ontvangers [&hellip;]&rdquo; (art. 4(9) AVG).</p>',

        'step_receiver_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de ontvangers in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">U dient hier de personen, afdelingen, organisaties of instanties waaraan de persoonsgegevens worden verstrekt, te vermelden. Voor ontvangers buiten uw organisatie, kunt u de betreffende organisatie benoemen evenals het betreffende onderdeel/de functiegroep, wanneer dat bij u bekend is. Indien mogelijk, is verder het advies om aan te geven waarom de betreffende ontvangers de persoonsgegevens ontvangen en eventueel welke gegevens het betreft.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> neem geen namen van individuele medewerkers op, het betreft enkel categorie&euml;n.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de ontvangers</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De ontvanger kan een organisatie (rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan), afdeling of een natuurlijke persoon zijn aan wie u persoonsgegevens verstrekt. De ontvanger kan zich daarnaast zowel binnen de organisatie van de verwerkingsverantwoordelijke bevinden, als erbuiten. Wanneer een ontvanger zich buiten de organisatie van de verwerkingsverantwoordelijke bevindt, kan deze de privacy positie hebben van de verwerker, derde of de betrokkene.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Voorbeelden van ontvangers</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Zoals hierboven reeds omschreven kunnen ontvangers zich zowel binnen als buiten de organisatie van de verwerkingsverantwoordelijke bevinden. Zie hieronder een aantal voorbeelden van ontvangers binnen en buiten de organisatie van de verwerkingsverantwoordelijke:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Afdelingen, teams of medewerkers die belast zijn met de verwerking van persoonsgegevens voor de uitvoering van hun taken of werkzaamheden, zoals:
                    <ul class="list-disc list-outside mb-4 ml-5">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Medewerkers van de communicatieafdeling die toegang hebben tot klantgegevens;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Medewerkers van de P&amp;O-afdeling die toegang hebben tot de personeelsdossiers;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Beveiligingsmedewerkers die toegang hebben tot de beelden van de beveiligingscamera&#39;s;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Functioneel beheerders die hiertoe toegang hebben tot de persoonsgegevens binnen een applicatie/systeem;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Leidinggevenden die structureel toegang hebben tot bijvoorbeeld persoonsgegevens in rapportages.</li>
                    </ul>
                </li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">(Sub)Verwerkers: De (sub)verwerkers die zijn opgenomen onder de tab &lsquo;Subverwerker&rsquo; in dit AVG-register dienen hier te worden &lsquo;herhaald&rsquo;.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Betrokkenen: De natuurlijke personen wiens persoonsgegevens worden verwerkt (zie ook art. 4(1) AVG). Aan hen zouden hun eigen gegevens ook structureel kunnen worden verstrekt. Denk aan medewerkers die hun salarisstrook ontvangen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Derden: De personen/partijen aan wie persoonsgegevens worden verstrekt en die niet zijn aan te merken als de verwerkingsverantwoordelijke, de verwerker, de betrokkene of de personen die onder het rechtstreekse gezag van de verwerkingsverantwoordelijke of verwerker gemachtigd zijn om persoonsgegevens te verwerken (zie ook art. 4(10) AVG). In tegenstelling tot de verwerker zal de &lsquo;derde&rsquo; de gegevens veelal voor diens eigen behoefte verwerken. Derden zijn bijvoorbeeld:
                    <ul class="list-disc list-outside mb-4">
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Concernonderdelen waarbinnen de doorgifte van persoonsgegevens plaatsvindt (zoals de doorgifte van personeelsgegevens van de dochtermaatschappij naar de moedermaatschappij);</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Verschillende Rijksonderdelen waarbinnen doorgifte van persoonsgegevens plaatsvindt;</li>
                        <li class="text-sm text-gray-950 dark:text-gray-300">Verzekeringsmaatschappijen, arbodiensten, incassobureaus etc.</li>
                    </ul>
                </li>
            </ul>',


        'step_processing_goal_title' => 'Informatie over Doel & Grondslag',
        'step_processing_goal_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG moeten persoonsgegevens alleen worden verzameld voor specifieke, expliciete en gerechtvaardigde doeleinden (art. 5(1)(b) AVG). Ook is een wettelijke basis nodig voor de verwerking (art. 6(1) AVG).</p>',
        'step_processing_goal_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over het doel in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan voor welke doeleinden u persoonsgegevens verwerkt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over het doel</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Doelen kunnen een deel van het proces betreffen, zoals het beoordelen van subsidieaanvragen. Dit betekent dat er doorgaans meerdere doeleinden van een verwerking zijn, of de doelomschrijving uit meerdere onderdelen bestaat.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de rechtsgrond</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Persoonsgegevens mogen alleen worden verwerkt als er een rechtsgrond is. Hier zijn de mogelijke rechtsgronden (art. 6 AVG):</p>
            <ol class="list-decimal list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Toestemming van de betrokkene (intrekbaar)</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitvoering of voorbereiding van een overeenkomst</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Voldoen aan een wettelijke verplichting</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Bescherming van vitale belangen</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitoefening van openbaar gezag of algemeen belang</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Gerechtvaardigde belangen van de verwerkingsverantwoordelijke of een derde</li>
            </ol>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Uitzondering op de rechtsgrond &lsquo;gerechtvaardigd belang&rsquo; voor de overheid</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Overheidsinstanties mogen het gerechtvaardigd belang niet als basis gebruiken voor gegevensverwerking binnen hun overheidstaken. Ze moeten een andere rechtsgrond hebben, meestal taak van algemeen belang.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">Voor &quot;bedrijfsmatige handelingen&quot;, zoals salarisadministratie, kunnen ze wel het gerechtvaardigd belang gebruiken.</p>',

        'step_stakeholder_data_title' => 'Informatie over Betrokkenen en gegevens',
        'step_stakeholder_data_info' => '
            <p class="text-sm text-gray-500">In de AVG wordt de persoon over wie informatie wordt verwerkt aangeduid als &quot;betrokkene&quot; (art. 4(1) AVG). Persoonsgegevens zijn de gegevens waarmee de betrokkene direct of indirect kan worden ge&iuml;dentificeerd.</p>',
        'step_stakeholder_data_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de betrokkenen en de gegevens in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan welke categorie&euml;n betrokkenen en persoonsgegevens u verwerkt. Duid de categorie&euml;n betrokkenen aan (zoals werknemers, sollicitanten) en geef aan welke persoonsgegevens u verwerkt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Per categorie betrokkenen specificeert u welke gegevens u verwerkt en waarom. Voeg doelen toe die u eerder in &quot;Doel &amp; Grondsldag&quot; hebt ingevuld.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over persoonsgegevens</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De AVG is all&eacute;&eacute;n van toepassing op persoonsgegevens. Persoonsgegevens zijn alle gegevens die:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Betreffen een persoon;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Identificeren een persoon direct;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Maken een persoon identificeerbaar.</li>
            </ul>
            <ul class="list-outside mb-4">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 1) De gegevens moeten over de persoon gaan.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 2) Gegevens identificeren een persoon als ze uniek zijn.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 3) Een persoon is identificeerbaar als zijn identiteit redelijkerwijs kan worden vastgesteld.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Ad 4) De AVG is alleen van toepassing op gegevens over natuurlijke personen, niet op rechtspersonen, dieren of zaken.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Meer informatie over persoonsgegevens en de betrokkene? Zie o.a. de website van de Autoriteit Persoonsgegevens over dit onderwerp.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over het &quot;Verzameldoel&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Omschrijf het doel waarvoor de gegevens oorspronkelijk zijn verzameld. Dit kan verschillen van het doel van de uiteindelijke verwerking.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Bewaartermijn&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bewaar persoonsgegevens niet langer dan nodig. Respecteer wettelijke bewaartermijnen. Voor overheidsverwerkingen geldt de Archiefwet 1995. Raadpleeg de toepasselijke selectielijst voor bewaartermijnen.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Bron&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Houd bij waar de persoonsgegevens vandaan komen. Dit kan rechtstreeks van de betrokkene zijn, maar ook van andere bronnen. Duid de herkomst helder aan.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Informatie over de &quot;Verplichte levering&quot;</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef duidelijk aan of de betrokkene verplicht is gegevens te verstrekken en de mogelijke gevolgen van niet-verstrekking.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over bijzondere persoonsgegevens, strafrechtelijke gegevens en BSN</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bijzondere persoonsgegevens zijn gegevens over ras, etniciteit, politieke voorkeur, religie, seksualiteit, lidmaatschap van een vakbond, genetische, biometrische, en gezondheidsgegevens.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Verwerking is in principe verboden, tenzij uitzonderingen gelden volgens de AVG (art. 9 AVG) en UAVG.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voorbeelden waarop de verwerking is toegestaan, zijn:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De betrokkene heeft hiervoor uitdrukkelijke toestemming gegeven;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor arbeidsrechtelijke zaken of zaken mb.t. sociale zekerheid;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De betrokkene heeft de gegevens voor dit doeleinde kennelijk openbaar gemaakt;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk in het kader van een gerechtelijke procedure;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De is noodzakelijk om aan een volkenrechtelijke verplichting te voldoen;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor arbeidsgeneeskunde;</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De verwerking is noodzakelijk voor de volksgezondheid.</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Vermeld de uitzondering die van toepassing is op het verwerken van bijzondere persoonsgegevens.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Let op:</span> Voor het verwerken van strafrechtelijke gegevens en Burgerservicenummers gelden strikte voorwaarden: zie artikel 10 AVG en 46 UAVG. Vermeld of voldaan wordt aan de voorwaarden. </p>',

        'step_decision_making_title' => 'Informatie over Besluitvorming',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">Conform de AVG heeft de betrokkene &ldquo;het recht niet te worden onderworpen aan een uitsluitend op geautomatiseerde verwerking, waaronder profilering, gebaseerd besluit waaraan voor hem rechtsgevolgen zijn verbonden of dat hem anderszins in aanmerkelijke make treft&rdquo; (art. 22(1) AVG). Er is sprake van geautomatiseerde besluitvorming wanneer persoonsgegevens worden gebruikt om tot een bepaalde beslissing over de betrokkene te komen, en deze beslissing genomen wordt zonder noemenswaardige inbreng van een mens.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Geautomatiseerde besluitvorming</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In dit onderdeel dient u aan te geven of er wel/geen sprake is van geautomatiseerde besluitvorming. Er is sprake van geautomatiseerde besluitvorming wanneer u of uw organisatie besluiten neemt over betrokkenen (bijvoorbeeld wel/geen recht op een vergunning), op geautomatiseerde wijze (de computer maakt de beslissing, niet een mens), en waaraan rechtsgevolgen zijn verbonden, of die hen anderszins in aanmerkelijke mate treffen, zonder enige menselijke tussenkomst.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Indien er sprake is van geautomatiseerde besluitvorming, dient u &lsquo;Ja&rsquo; aan te klikken. Vervolgens dient u informatie in te vullen over:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De achterliggende logica (u kunt hierbij denken aan informatie over hoe de gegevens worden gebruikt om tot het besluit te komen, welke wiskundige en statistische procedures worden gevolgd, welke beoordelings- of selectieregels worden toegepast om te resulteren in het uiteindelijke besluit);</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Het belang en de verwachte gevolgen van de verwerking voor de betrokkenen (bijvoorbeeld wel/geen toekenning van een vergunning).</li>
            </ul>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verbod</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Het is in beginsel verboden om betrokkenen te onderwerpen aan geautomatiseerde besluitvorming, waaronder profilering, wanneer dit rechtsgevolgen heeft voor de betrokkene, of dit de betrokkene anderszins in aanzienlijke mate treft. Denk aan besluiten die door de computer worden genomen waardoor een arbeidscontract wordt opgezegd, een leverancier geen overeenkomst mag aangaan met een overheidsdienst, of iemand een sollicitatiegesprek wordt ontzegd. Een voorbeeld van profilering die betrokkenen in aanzienlijke mate treft, is het opstellen van een kredietwaardigheidsprofiel, op basis waarvan vervolgens wordt besloten of aan iemand wel/geen krediet wordt verstrekt.</p>',

        'step_system_title' => 'Informatie over Applicaties en Systemen',
        'step_system_info' => '
            <p class="text-sm text-gray-500">Vanuit het oogpunt van beveiliging van de persoonsgegevens en om het eventueel herstel daarvan in geval van (bijvoorbeeld) een datalek te optimaliseren, is het van belang om te weten met welk informatiesysteem of applicatie de verwerking plaatsvindt. U dient zich hierbij te realiseren dat een informatiesysteem of applicatie niet hetzelfde is als een verwerking van persoonsgegevens.</p>',
        'step_system_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul namen van informatiesystemen/applicaties in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Zoek het systeem / de applicatie door te beginnen met typen. Selecteer de applicatie uit de lijst. Indien het systeem nog niet is ingevoerd: voer hier de naam in van het(/de) informatiesysteem(en) of de applicatie(s) door middel waarvan de verwerking plaatsvindt. Let a.u.b. op de juiste spelling. Dit in verband met de zoekfunctie in dit register.</p>',

        'step_security_title' => 'Informatie over Beveiliging',
        'step_security_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG moeten de verwerker, (sub)verwerker en de verwerkingsverantwoordelijke passende technische en organisatorische maatregelen nemen (artikel 32 AVG).</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Toelichting maatregelen</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Beschrijf hier, indien relevant, kort de technische en organisatorische beveiligingsmaatregelen voor persoonsgegevens, zoals encryptie, wachtwoorden, versleutelde verbindingen, autorisatiematrix, beleid en fysieke toegangscontrole. Deze beschrijving hoeft niet gedetailleerd te zijn.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> In de verwerkersovereenkomst worden vaak specifieke afspraken over beveiligingsmaatregelen gemaakt. Je kunt deze overnemen in het register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonimiseren</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Indien er sprake is van het pseudonimiseren van de gegevens, vul hier in welke gegevens gepseudonimiseerd worden en welke methodieken hiervoor worden toegepast.</p>',

        'step_passthrough_title' => 'Informatie over Doorgifte',
        'step_passthrough_info' => '
            <p class="text-sm text-gray-500 mb-4">Niet elk land beschermt persoonsgegevens op dezelfde manier. Binnen de Europese Economische Ruimte (EER) zijn de regels vergelijkbaar, waardoor persoonsgegevens hier vrij kunnen worden doorgegeven. Buiten de EER is dit anders. Doorgifte naar landen buiten de EER is alleen toegestaan als er passende waarborgen zijn (zie ook hoofdstuk V AVG).</p>
            <p class="text-sm text-gray-500 mb-4">De EER omvat alle EU-landen plus Noorwegen, Liechtenstein en IJsland. Deze drie landen hebben een vergelijkbaar niveau van gegevensbescherming. Persoonsgegevens uit Nederland mogen zonder extra waarborgen worden doorgegeven binnen de EER.</p>
            <p class="text-sm text-gray-500">Overigens kan hier ook het Rijksbeleid inzake cloud van toepassing zijn, indien de gegevens in de cloud van een derde land worden verwerkt (opgeslagen).</p>',
        'step_passthrough_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de doorgifte in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Geef aan of je persoonsgegevens doorgeeft aan landen buiten de EER of aan internationale organisaties. Indien het geval, geef dan aan naar welke landen je gegevens doorgeeft. Controleer op de website van de Europese Commissie of het betreffende land een passend beschermingsniveau heeft.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">De passende waarborgen</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De volgende waarborgen bij doorgifte aan een derde land of internationale (volkenrechtelijke) organisatie (denk aan de EU-instituties, de Verenigde Naties, etc) kunnen worden getroffen:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Adequaatheidsbesluit door de Europese Commissie waarbij zij heeft besloten dat deze landen een passend beschermingsniveau waarborgen (art. 45 AVG). <span class="font-bold">Let op:</span> adequaatheidsbesluiten kunnen per direct worden ingetrokken door bijvoorbeeld het Hof van Justitie van de Europese Unie</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Juridisch bindend en afdwingbaar besluit tussen overheidsinstanties of -organen (art. 46(2)(a) en 46(3)(b) AVG en overweging 108 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Bindende bedrijfsvoorschriften (binding corporate rules) binnen een concern of groep van ondernemingen (art. 46(3)(b) en 47 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Partijen zijn de standaardbepalingen van de Europese Commissie (standard contractual clauses) inzake gegevensbescherming overeengekomen (art. 46(2)(c) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Partijen zijn de standaardbepalingen van de toezichthouder (voor Nederland: de Autoriteit Persoonsgegevens) overeengekomen (art. 46(2)(d) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is een goedgekeurde gedragscode van toepassing (art. 40 en 46(2)(e) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is sprake van een rechtelijke uitspraak of besluit van een bestuursorgaan gebaseerd op een verdrag (art. 48 AVG.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">De toezichthouder (voor Nederland: de Autoriteit Persoonsgegevens) heeft toestemming gegeven voor de doorgifte op basis van contractsbepalingen tussen partijen of bepalingen in administratieve regelingen tussen overheidsinstanties of -organen (art. 46(3) AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Er is sprake van een rechtelijke uitspraak of besluit van een administratieve autoriteit welke is gebaseerd op een internationale overeenkomst (art. 48 AVG.</li>
            </ul>',

        'step_geb_dpia_title' => 'Informatie over GEB (DPIA)',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">Een GEB of Data Protection Impact Assessment (DPIA) is een voorafgaande toetsing aan een verwerking die minimaal aan artikel 35 AVG moet voldoen, en na advies van de Functionaris Gegevensbescherming (FG) op managementniveau moet zijn vastgesteld. Voor het register van verwerkingsactiviteiten voert het te ver om inhoud en proces verdergaand uit te leggen.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Hoog Risico? Dan een GEB (DPIA)!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerkingsverantwoordelijke is verplicht om een GEB (DPIA) uit te voeren voor verwerkingen die een hoog risico met zich meebrengen voor de betrokkenen. Denk bijvoorbeeld aan een nieuwe verwerking of een reeds bestaande verwerking met een gewijzigd risico met bijzondere persoonsgegevens (zoals gezondheidsgegevens).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">U kunt hier aangeven of er een GEB (DPIA) is uitgevoerd t.a.v. de verwerkingen die door de organisatie worden verricht. Zo ja, dan kunt u deze GEB (DPIA) (indien beschikbaar) ook toevoegen aan deze verwerking onder de tab &#39;Documenten &amp; Bijlagen&rsquo;.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Raadpleeg onderstaande referenties voor een nadere uitleg van &quot;hoog risico&quot; en de criteria of een GEB (DPIA) voor een verwerkingsverantwoordelijke verplicht is.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Staatscourant van het Koninkrijk der Nederlanden, Besluit inzake lijst van verwerkingen van persoonsgegevens waarvoor een gegevensbeschermingseffectbeoordeling ( GEB (DPIA)) verplicht is.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitleg over de 9 criteria uit de WP248 artikel 29 richtsnoeren 9 criteria voor verwerkingen waarvoor een GEB (DPIA) moet worden uitgevoerd (pagina 10 tot en met 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Informatie van de AP over de voorwaarden en het uitvoeren van PIA&rsquo;s.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300">Maak voor het beantwoorden van de vragen zonodig gebruik van intern ondersteuningsmateriaal binnen uw organisatie (indien aanwezig), of raadpleeg de Functionaris voor gegevensbescherming of de Privacy Officer (indien aanwezig).</li>
            </ul>',

        'step_contact_person_title' => 'Informatie over Contactpersoon',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">Bij een verwerking moet worden aangegeven wie voor deze verwerking de contactpersoon is. De gegevens van de contactpersoon zijn voor intern gebruik en worden niet op de openbare website gepubliceerd.</p>',

        'step_attachments_title' => 'Informatie over Bijlagen',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">Als u documenten toevoegt, mag het ook om conceptversies gaan. Geef duidelijk aan wat voor soort document het is en of het concept of definitief is. Toevoegen, wijzigen of verwijderen van documenten vereist geen nieuwe versie van de verwerking. Voeg opmerkingen toe voor verduidelijking.</p>',

        'step_remarks_title' => 'Informatie over Opmerkingen',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500" mb-4>Voeg opmerkingen toe aan de verwerking. Deze zijn intern en worden niet openbaar gemaakt.</p>
            <p class="text-sm text-gray-500">Het kunnen notities zijn over de betrokken medewerker, herinneringen, mededelingen, openstaande zaken, mutatiedata, verwijzingen naar andere verwerkingen of documenten zoals PIA&#39;s, verwerkersovereenkomsten, vastgestelde regelingen bij meerdere verwerkingsverantwoordelijken, beheermaatregelen op basis van een GEB/PIA, of datalekken met het informatiesysteem, enz.</p>',
        'step_remarks_extra_info' => '',
    ],

    'wpg_processing_record' => [
        'step_processing_name_title' => 'Informatie over Naam Verwerking',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Vul hier de naam van de gegevensverwerking in zoals bekend binnen jouw organisatie, zoals  "subsidieadministratie" of "nieuwsbrieven sturen".</p>',
        'step_processing_name_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Leg verwerkingen vast</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Alle handelingen met persoonsgegevens, zoals opslaan, doorgeven of vernietigen, gelden als &quot;verwerking&quot;. Registreer deze in het WPG Verantwoordelijke register. Categoriseer activiteiten zoals &quot;subsidieadministratie&quot;, &quot;nieuwsbrieven sturen&quot; of &quot;portaal bieden&quot; (inclusief verzamelen, opslaan, delen en vernietigen van gegevens).</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerking</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">WPG definieert gegevensverwerking als &quot;elke bewerking of elk geheel van bewerkingen met betrekking tot politiegegevens of een geheel van politiegegevens, al dan niet uitgevoerd op geautomatiseerde wijze, zoals het verzamelen, vastleggen, ordenen, structureren, opslaan, bijwerken of wijzigen, opvragen, raadplegen, gebruiken, verstrekken door middel van doorzending, verspreiden of op andere wijze ter beschikking stellen, samenbrengen, met elkaar in verband brengen, afschermen of vernietigen van politiegegevens.&quot; (art. 1(c) WPG).</p>',

        'step_responsible_title' => 'Informatie over Verwerkingsverantwoordelijke',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Bij of krachtens de WPG wordt de verwerkingsverantwoordelijke aangewezen die formeel verantwoordelijk is voor goede uitvoering van de WPG.</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Kies de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Zoek de verwerkinsverantwoordelijke door te beginnen met typen. Indien de verwerkinsverantwoordelijke nog niet in het systeem zit, druk op het &#39;+&#39; teken en voer de gegevens van de verwerkingsverantwoordelijke in. Vul de functie in en mogelijk contactdetails.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voeg meerdere verwerkingsverantwoordelijken toe indien nodig, bijv. voor samenwerkingsverbanden.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerkingsverantwoordelijke voor uitvoering van de WPG door de Koninklijke marechaussee is de Minister van Defensie. Bij de bijzondere opsporingsdiensten is dit de minister van het departement waaronder de bijzondere opsporingsdienst ressorteert. De verwerkingsverantwoordelijke voor de buitengewoon opsporingsambtenaar (Boa) is de werkgever van de desbetreffende Boa.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300"><span class="font-bold">Let op</span>: De verwerkingsverantwoordelijke is niet de verwerker van de politiegegevens. Een verwerker is ’een natuurlijke persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat ten behoeve van de verwerkingsverantwoordelijke politiegegevens verwerkt’ (art. 6(c) WPG). Dit is vaak een persoon of organisatie aan wie de verwerkingsverantwoordelijke de gegevensverwerking heeft uitbesteed. Een verwerker is niet zelfstandig verantwoordelijk voor de verwerking van de politiegegevens, maar heeft wel een aantal afgeleide verplichtingen. Zo dient de verwerker de instructies van de verwerkingsverantwoordelijke te volgen en moet de verwerker garanderen dat passende technische en organisatorische beveiligingsmaatregelen worden geïmplementeerd. In een aparte vraag kunt u de gegevens van een eventuele verwerker opgeven.</p>',

        'step_processor_title' => 'Informatie over Verwerker',
        'step_processor_info' => '
            <p class="text-sm text-gray-500 mb-4">De WPG definieert de verwerker als: “een natuurlijke persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/ dat ten behoeve van de verwerkingsverantwoordelijke politiegegevens verwerkt” (art. 6(c) WPG).</p>
            <p class="text-sm text-gray-500">Dit is vaak een persoon of organisatie aan wie de verwerkingsverantwoordelijke de gegevensverwerking heeft uitbesteed. Een voorbeeld is een aanbieder van gegevensopslag in een applicatie die door die aanbieder wordt beheert; wanneer de dienst puur ziet op het opslaan van gegevens, dan is de aanbieder een verwerker.</p>',
        'step_processor_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerker</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerker kan een rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan, dan wel een natuurlijke persoon zijn. Overheidsinstanties, een dienst of ander orgaan zijn bijvoorbeeld bestuursorganen (zoals ministers en colleges van burgemeester & wethouders), zelfstandige bestuursorganen (ZBO’s) of gemeenschappelijke regelingen.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerker is een partij die ten behoeve van de verwerkingsverantwoordelijke een verwerking verricht, en die niet onder het rechtstreekse gezag van de verwerkingsverantwoordelijke valt. Om te beoordelen of een partij is aan te merken als een verwerker, dient u te kijken naar de concrete activiteiten die in een specifieke context door een ingeschakelde partij worden verricht.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een partij verwerkt de politiegegevens ten behoeve van een andere partij wanneer deze enkel een uitvoerende taak heeft en geen zeggenschap over het doel waarvoor de verwerking van persoonsgegevens plaatsvindt. De verwerker volgt bij de verwerking de instructies van de verwerkingsverantwoordelijke op.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Daarnaast valt een verwerker niet onder het rechtstreekse gezag van een verwerkingsverantwoordelijke. Dit betekent dat de verwerker niet onder de juridische entiteit van de verwerkingsverantwoordelijke valt. Een medewerker van de verwerkingsverantwoordelijke is dus bijvoorbeeld geen verwerker (want valt onder diens rechtstreekse gezag). Een ZZP’er is daarentegen wel een verwerker (want een aparte juridische entiteit).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> In principe bepaalt de verwerkingsverantwoordelijke het doel en de (essentiële kenmerken van de) middelen van de verwerking, en voert de verwerker de verwerking enkel uit. In de praktijk kiezen verwerkers echter vaak wel zelf welke (technische) middelen zij gebruiken voor de verwerking (zoals welke software of hardware). Zo lang zij echter niet het doel van de verwerking bepalen, en bijvoorbeeld niet welke gegevens zij verzamelen, van wie en hoe lang zij deze bewaren, dan blijven zij aan te merken als verwerker. De verwerkingsverantwoordelijke bepaalt hoe ver het mandaat van de verwerker hierin strekt.</p>

            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verwerkingsovereenkomst</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Tussen een verwerkingsverantwoordelijke en een verwerker dient een overeenkomst of andere bindende rechtshandeling (zie ook art. 6c lid 2 WPG en art. 6:1b Besluit politiegegevens) te worden gesloten, om te borgen dat de verwerker zorgvuldig met de politiegegevens van de verwerkingsverantwoordelijke zal omgaan. Veelal wordt dit de ‘verwerkersovereenkomst’ genoemd. In de verwerkersovereenkomst dient u een aantal zaken te regelen, zoals over het doel van en de zeggenschap over de verwerking, procedures en de beveiliging van de persoonsgegevens.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> De verwerkersovereenkomst kunt u opslaan onder de tab ‘Documenten & Bijlagen’ in dit register.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Daarnaast zijn er enkele plichten in de WPG die (ook) zelfstandig gericht zijn aan de verwerker. Het gaat dan om de beveiliging van gegevens, het bijhouden van een register van verwerkingsactiviteiten en het toepassen van de uitgangspunten met betrekking tot Privacy by Design. Verwerkers moeten dus zelf ook een register van verwerkingsactiviteiten aanleggen, met daarin soortgelijke gegevens zoals in het register van de verwerkingsverantwoordelijke zijn opgenomen. En last but not least rust op hen een eigen beveiligingsverplichting en zijn zij zelf aansprakelijk voor schade die voortvloeit uit de overeengekomen werkzaamheden.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300">Een verwerkingsverantwoordelijke mag alleen verwerkers inschakelen die afdoende garanties met betrekking tot de naleving van de WPG kunnen garanderen. U moet zich er daarom van vergewissen dat de garanties worden geboden, die nodig zijn gelet op de privacy risico’s die de verwerkingen die u wilt uitbesteden, met zich meebrengen. Hier moeten de IB-risicoanalyses en gegevenbeschermingseffectbeoordelingen (GEB/DPIA’s) een belangrijke rol bij spelen.</p>',

        'step_receiver_title' => 'Informatie over Ontvanger',
        'step_receiver_info' => '
            <p class="text-sm text-gray-500">In de WPG en onderliggende regelgeving is geregeld dat politiegegevens in bepaalde gevallen en voor specifieke doeleinden kunnen worden overgedragen aan andere personen of instanties. In dit register worden deze samengebracht onder de noemer ‘ontvangers’.</p>',
        'step_receiver_extra_info' => '
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De WPG voorziet erin dat politiegegevens verstrekt kunnen worden aan personen of instanties in Nederland die niet onder de reikwijdte van de WPG vallen, maar die voor de uitoefening van hun taak bepaalde politiegegevens nodig hebben. Uitgangspunt is dat politiegegevens niet verstrekt mogen worden aan derden buiten het WPG-domein, tenzij dit in de WPG of onderliggende regelgeving is geregeld. (art. 16, 17, 18, 19, 20, 22, 23 en 24 WPG).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Als politiegegevens verstrekt worden aan personen in Nederland die eveneens belast zijn met de uitvoering van een wettelijke strafrechtelijke opsporingstaak, valt dit onder de definitie van het ter beschikking stellen van politiegegevens. Politiegegevens mogen enkel ter beschikking worden gesteld aan personen in Nederland die geautoriseerd zijn overeenkomstig de bepalingen van de WPG (art. 15 WPG). Ook kunnen politiegegevens ter beschikking worden gesteld aan opsporingsinstanties in andere lidstaten van de Europese Unie die de ‘Richtlijn gegevensbescherming opsporing en vervolging’ hebben geïmplementeerd in hun nationale wetgeving en aan Europese landen die geen EU-lidstaat zijn maar wel lid zijn van het Verdrag van Schengen (bijv. Noorwegen, IJsland en Zwitserland). Politiegegevens kunnen ook ter beschikking worden gesteld aan organen en instanties die ingevolge Europees recht belast zijn met strafrechtelijke taken; bijvoorbeeld Europol en Eurojust (art. 15(a) WPG).</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Onder strikte voorwaarden mogen politiegegevens worden verstrekt aan opsporingsinstanties in landen die niet onder de werkingssfeer van de ‘Richtlijn gegevensbescherming opsporing en vervolging’ vallen of aan internationale organisaties die belast zijn met strafrechtelijke taken anders dan in Europees verband; bijvoorbeeld Interpol (art. 17(a) WPG). Deze vorm van verstrekken is gedefinieerd als de doorgifte van politiegegevens.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Overdracht van politiegegevens</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">Het verstrekken, ter beschikking stellen en doorgeven van politiegegevens omvat iedere vorm van het bekend maken of overdragen van politiegegevens, ongeacht de wijze waarop dit gebeurt. Het kan schriftelijk of langs elektronische weg gebeuren maar ook door het overhandigen van een magneetband met gegevens. De overdracht van politiegegevens kan ook plaatsvinden door het (laten) raadplegen van de gegevens.</p>
        ',

        'step_wpg_goal_title' => 'Informatie over Doel & Grondslag',
        'step_wpg_goal_info' => '
            <p class="text-sm text-gray-500 mb-4">De WPG geeft als beginsel dat politiegegevens enkel voor welbepaalde, uitdrukkelijk omschreven doeleinden mogen worden verwerkt.</p>
            <p class="text-sm text-gray-500">Als uitgangspunt geldt dat het verwerken van politiegegevens in beginsel slechts is toegestaan als die plaatsvindt in het kader van de negen in de WPG omschreven doelen.</p>',

        'step_special_police_data_title' => 'Informatie over Bijzondere Politiegegevens',
        'step_special_police_data_info' => '
            <p class="text-sm text-gray-500">Bijzondere politiegegevens mogen enkel onder voorwaarden worden verwerkt. De verwerking moet onvermijdelijk zijn voor het doel ervan, in aanvulling zijn op de verwerking van andere politiegegevens betreffende de persoon en de gegevens moeten extra zijn beveiligd op een passende wijze.</p>',

        'step_decision_making_title' => 'Informatie over Besluitvorming',
        'step_decision_making_info' => '
            <p class="text-sm text-gray-500">In artikel 7a van de WPG is bepaald dat een besluit dat uitsluitend op geautomatiseerde verwerking is gebaseerd, met inbegrip van profilering, dat voor de betrokkene nadelige rechtsgevolgen heeft of hem in aanmerkelijke mate treft, verboden is, tenzij wordt voorzien in voorafgaande menselijke tussenkomst door of namens de verwerkingsverantwoordelijke en in specifieke voorlichting aan de betrokkene. Onder profilering wordt in de WPG verstaan: elke vorm van geautomatiseerde verwerking van persoonsgegevens waarbij aan de hand van die gegevens bepaalde persoonlijke aspecten van een natuurlijke persoon worden geëvalueerd, met de bedoeling met name aspecten betreffende zijn beroepsprestaties, economische situatie, gezondheid, persoonlijke voorkeuren, interesses, betrouwbaarheid, gedrag, locatie of verplaatsingen te analyseren of te voorspellen.</p>',
        'step_decision_making_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Geautomatiseerde besluitvorming</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">In dit onderdeel dient u aan te geven of er wel/geen sprake is van geautomatiseerde besluitvorming. Er is sprake van geautomatiseerde besluitvorming wanneer u of uw organisatie besluiten neemt over betrokkenen (bijvoorbeeld wel/geen recht op een vergunning), op geautomatiseerde wijze (de computer maakt de beslissing, niet een mens), en waaraan rechtsgevolgen zijn verbonden, of die hen anderszins in aanmerkelijke mate treffen.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Indien er sprake is van geautomatiseerde besluitvorming, dient u dit aan te geven. Vervolgens dient u nuttige informatie in te vullen over de onderliggende logica, alsmede het belang en de verwachte gevolgen van die verwerking voor de betrokkene. Betrokkenen die het treft moeten hierover geïnformeerd worden. Maar onder bepaalde voorwaarden kan de verwerkingsverantwoordelijke deze informatieplicht uitstellen, beperken of achterwege laten voor zover dit een noodzakelijke en evenredige maatregel is. Bijvoorbeeld als de informatie nadelige gevolgen heeft voor de opsporing. Als hiervan gebruik wordt gemaakt, dient hiermee rekening te worden gehouden bij de opname van de verwerking in het register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verbod</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Het is in beginsel verboden om betrokkenen te onderwerpen aan geautomatiseerde besluitvorming, waaronder profilering, wanneer dit nadelige rechtsgevolgen heeft voor de betrokkene, of dit de betrokkene anderszins in aanzienlijke mate treft.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Wanneer is geautomatiseerde besluitvorming toegestaan?</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Het verbod op geautomatiseerde besluitvorming (waaronder profilering) kent echter wel uitzonderingen, namelijk wanneer wordt voorzien in voorafgaande menselijke tussenkomst door of namens de verwerkingsverantwoordelijke. Een dergelijk besluit mag niet worden gebaseerd op bijzondere categorieën politiegegevens (art. 5 WPG) tenzij de Autoriteit persoonsgegevens over de voorgenomen verwerking is geraadpleegd. Profilering op basis van bijzondere categorieën politiegegeven die leidt tot discriminatie van personen, is sowieso niet toegestaan.</p>',

        'step_system_application_title' => 'Informatie over Systemen / Applicaties',
        'step_system_application_info' => '
            <p class="text-sm text-gray-500">Vanuit het oogpunt van beveiliging van de persoonsgegevens en om het eventueel herstel daarvan in geval van (bijvoorbeeld) een datalek te optimaliseren, is het van belang om te weten met welk informatiesysteem of applicatie de verwerking plaatsvindt. U dient zich hierbij te realiseren dat een informatiesysteem of applicatie niet hetzelfde is als een verwerking van persoonsgegevens.</p>',
        'step_system_application_extra_info' => '
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Verwerkingen van politiegegevens zijn altijd gericht op bepaalde doeleinden die voortvloeien uit de doelverwerkingen zoals die gegeven zijn in de artikelen 8, 9, 10, 12 of 13 WPG. Voorbeeld van verwerkingen van politiegegevens is een grootschalige opsporingszaak (art. 9 WPG). Om het doel van een verwerking van politiegegevens te bereiken, kunnen voor de bewerkingen een of meerdere systemen worden gebruikt met bepaalde hardware, software en data(bestanden). Omgekeerd kunnen via een systeem meerdere “verwerkingen van politiegegevens” worden uitgevoerd. Om de politiegegevens goed te kunnen beveiligingen is het van belang om het overzicht te hebben welke systemen worden gebruikt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul namen van systemen/applicaties in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voer hier de naam in van het(/de) informatiesysteem(en) of de applicatie(s) door middel waarvan de verwerking plaatsvindt. U kunt meerdere systemen invullen. Let a.u.b. op de juiste spelling. Dit in verband met de zoekfunctie in dit register.</p>',

        'step_security_title' => 'Informatie over Beveiliging',
        'step_security_info' => '
            <p class="text-sm text-gray-500">De verwerkingsverantwoordelijke en de verwerker moeten ingevolge art. 4a WPG passende technische en organisatorische maatregelen treffen om: a. te waarborgen en te kunnen aantonen dat de verwerking van politiegegevens wordt verricht in overeenstemming hetgeen in de WPG is bepaald; b. het gegevensbeschermingsbeleid en de gegevensbeschermingsbeginselen op een doeltreffende manier uit te voeren en toe te passen; c. bij de bepaling van de verwerkingsmiddelen en de verwerking zelf de nodige waarborgen, zoals pseudonimisering, in de verwerking in te bouwen ter naleving van de WPG en ter bescherming van de rechten van de personen waarover politiegegevens worden verwerkt. Daarbij dienen de technische en organisatorische maatregelen zodanig te worden ingericht dat een beveiligingsniveau wordt gewaarborgd dat op het risico is afgestemd, met name met betrekking tot de verwerking van de bijzondere categorieën politiegegevens en op een zodanige manier dat de politiegegevens beschermd zijn tegen ongeoorloofde of onrechtmatige verwerking en tegen opzettelijk verlies, vernietiging of beschadiging.</p>',
        'step_security_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Vul informatie over de beveiligingsmaatregelen in</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De informatie die u invult onder ‘Beveiliging’ is bedoeld om een algemene beschrijving te geven van de technische en organisatorische beveiligingsmaatregelen die er zijn genomen om de politiegegevens op passende wijze zowel technisch (bijv. encryptie, wachtwoorden, versleutelde verbindingen etc.) als organisatorisch (bijv. autorisatiematrix, beleid en fysieke toegangscontrole) te beveiliging. In dit register hoeft enkel een algemene beschrijving van de beveiligingsmaatregelen te worden opgenomen. De informatie in dit register is niet bedoeld om bijvoorbeeld één op één overeen te komen met de inhoud van een ISMS-systeem, of om zo’n systeem te vervangen.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Pseudonimisering</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300">In de Wpg wordt pseudonomisering niet gedefinieerd, maar wel genoemd als een maatregel om de rechten van betrokken te waarborgen. Onder pseudonimisering wordt in artikel 3 aanhef en sub 5 van de Richtlijn gegevensbescherming opsporing en vervolging verstaan het verwerken van persoonsgegevens op zodanige wijze dat de persoonsgegevens niet meer aan een specifieke betrokkene kunnen worden gekoppeld zonder dat er aanvullende gegevens worden gebruikt, mits deze aanvullende gegevens apart worden bewaard en technische en organisatorische maatregelen worden genomen om ervoor te zorgen dat de persoonsgegevens niet aan een geïdentificeerde of identificeerbare natuurlijke persoon worden gekoppeld.</p>',

        'step_geb_dpia_title' => 'Informatie over GEB (DPIA)',
        'step_geb_dpia_info' => '
            <p class="text-sm text-gray-500">In de praktijk worden verschillende termen voor dit voorafgaande onderzoek door elkaar gebruikt zoals PIA (privacy impact assessment), GEB of GBEB (gegevensbeschermingseffectbeoordeling) en DPIA (data protection impact assessment). Dit komt echter allemaal op hetzelfde neer, namelijk een schriftelijk onderzoek om te voldoen aan de verplichting uit de Wpg om ten aanzien van verwerkingen die waarschijnlijk een hoog risico opleveren voor de privacybescherming van personen, deze risico’s ik kaart te brengen en risico mitigerende maatregelen te benoemen. In de Wpg is aan de verwerkingsverantwoordelijke de opdracht toegekend om te beoordelen of de risicobeperkende maatregelen ook daadwerkelijk zijn uitgevoerd.</p>',
        'step_geb_dpia_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Hoog Risico? Dan een GEB (DPIA)!</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerkingsverantwoordelijke is verplicht om een GEB (DPIA) uit te voeren voor verwerkingen die een hoog risico met zich meebrengen voor de betrokkenen. Als verwerker dient u de verwerkingsverantwoordelijke te ondersteunen bij het uitvoeren van een GEB (DPIA), als deze hierom verzoekt. Deze ondersteuning kan zich bijvoorbeeld uitten in het aanleveren van gevraagde informatie over verwerkingen die door de verwerker worden verricht. Specifieke afspraken over bijvoorbeeld de kostenverdeling bij het ondersteunen bij een GEB (DPIA) door de verwerker, kunnen in een verwerkersovereenkomst zijn gemaakt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">U kunt hier aangeven of de verwerkingsverantwoordelijke een GEB (DPIA) heeft uitgevoerd t.a.v. de verwerkingen die door u worden verricht. Zo ja, dan kunt u deze GEB (DPIA) (indien beschikbaar) ook toevoegen aan uw AVG-register onder de tab ‘Documenten & Bijlagen’.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Raadpleeg onderstaande referentis voor een nadere uitleg van "hoog risico" en de criteria of een GEB (DPIA) voor een verwerkingsverantwoordelijke verplicht is.</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Staatscourant van het Koninkrijk der Nederlanden, Besluit inzake lijst van verwerkingen van persoonsgegevens waarvoor een gegevensbeschermingseffectbeoordeling ( GEB (DPIA)) verplicht is.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Uitleg over de 9 criteria uit de WP248 artikel 29 richtsnoeren 9 criteria voor verwerkingen waarvoor een GEB (DPIA) moet worden uitgevoerd (pagina 10 tot en met 13).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Informatie van de AP over de voorwaarden en het uitvoeren van PIA’s.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Maak voor het beantwoorden van de vragen zonodig gebruik van intern ondersteuningsmateriaal binnen uw organisatie (indien aanwezig), of raadpleeg de Functionaris voor gegevensbescherming of de Privacy Officer (indien aanwezig).</li>
            </ul>',

        'step_contact_person_title' => 'Informatie over Contactpersoon',
        'step_contact_person_info' => '
            <p class="text-sm text-gray-500">Bij een verwerking moet worden aangegeven wie voor deze verwerking de contactpersoon is. De contactpersoon is in de regel degene die gemachtigd is om dit register in te vullen en te gebruiken. De medewerkers die de verwerkingen reviewen of de FG kunnen contact opnemen met deze persoon indien er vragen zijn over de verwerking.</p>',

        'step_attachments_title' => 'Informatie over Documenten & Bijlagen',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">Als u documenten toevoegt, mag het ook om conceptversies gaan. Geef duidelijk aan wat voor soort document het is en of het concept of definitief is. Toevoegen, wijzigen of verwijderen van documenten vereist geen nieuwe versie van de verwerking. Voeg opmerkingen toe voor verduidelijking.</p>',

        'step_remarks_title' => 'Informatie over Opmerkingen',
        'step_remarks_info' => '
            <p class="text-sm text-gray-500 mb-4">Voeg opmerkingen toe aan de verwerking. Deze zijn intern en worden niet openbaar gemaakt.</p>
            <p class="text-sm text-gray-500">Het kunnen notities zijn over de betrokken medewerker, herinneringen, mededelingen, openstaande zaken, mutatiedata, verwijzingen naar andere verwerkingen of documenten zoals PIA\'s, verwerkersovereenkomsten, vastgestelde regelingen bij meerdere verwerkingsverantwoordelijken, beheermaatregelen op basis van een GEB/PIA, of datalekken met het informatiesysteem, enz.</p>',
        'step_remarks_extra_info' => '',

        'step_categories_involved_title' => 'Informatie over Categoriën Betrokkenen',
        'step_categories_involved_info' => '
            <p class="text-sm text-gray-500">Voor zover mogelijk moet een duidelijk onderscheid worden gemaakt tussen politiegegevens betreffende verschillende categorieën van betrokkenen. Bij de verwerking van deze gegevens moet er rekening mee worden gehouden dat een categorie kan wijzigen ten aanzien van een bepaalde natuurlijke persoon.</p>',
    ],

    'algorithm_record' => [
        'step_processing_name_title' => 'Informatie over Naam Algoritme',
        'step_processing_name_info' => '
            <p class="text-sm text-gray-500">Vul hier de naam van het algoritme in zoals bekend binnen jouw organisatie.</p>',
        'step_processing_name_extra_info' => '...',

        'step_responsible_use_title' => 'Informatie over Verantwoord Gebruik',
        'step_responsible_use_info' => '
            <p class="text-sm text-gray-500">Beschrijf bij "Doel en impact" het doel van het algoritme, het problemen dat met het algoritme wordt opgelost en de verwachte voordelen. Bij "Afwegingen" noteer je de ethische overwegingen en afwegingen bij de ontwikkeling. Geef bij "Menselijke tussenkomst" aan hoe en wanneer menselijke tussenkomst plaatsvindt en licht de risicobeheersmaatregelen toe bij "Risicobeheer". Vermeld de "Wettelijke basis" en plaats een "Link naar wettelijke basis". Voeg een "Aanmeldlink verwerker" toe. Beschrijf bij "Impacttoetsen" de uitgevoerde impacttoetsen, voeg "Links naar impacttoetsen" toe en geef een samenvatting van deze toetsen bij "Omschrijving impacttoetsen".</p>',

        'step_mechanics_title' => 'Informatie over Werking',
        'step_mechanics_info' => '
            <p class="text-sm text-gray-500">Beschrijf bij "Gegevens" welke data door het algoritme wordt gebruikt. Voeg bij "Links naar gegevensbronnen" de relevante bronnen toe. Leg bij "Technische werking" uit hoe het algoritme functioneert en welke technieken het gebruikt. Vermeld bij "Leverancier" de naam van de leverancier van het algoritme. Voeg bij "Link naar broncode" een link toe naar de broncode van het algoritme, indien beschikbaar.</p>',

        'step_meta_title' => 'Informatie over Metadata',
        'step_meta_info' => '
            <p class="text-sm text-gray-500">Geef bij "Taal" aan in welke programmeertaal het algoritme is geschreven. Beschrijf bij "Schema" de structuur en opbouw van het algoritme. Geef bij "Datum van ontwikkeling" aan wanneer een algoritme ontwikkeld is, helpt AI compliance officers bij overzicht recent of nog te ontwikkelen algoritmes. Geef bij "Eigenaar van het algoritme" aan wie de eindverantwoordelijk is (bijv. proceseigenaar). Geef bij "Product owner van het algoritme" aan wie operationeel verantwoordelijk is voor beheer en doorontwikkeling. Vermeld bij "Landelijk-ID" het identificatienummer dat nationaal aan het algoritme is toegewezen. Noteer bij "Bron-ID" het identificatienummer van de specifieke bron van het algoritme. Voeg bij "Tags" relevante trefwoorden toe die het algoritme beschrijven.</p>',

        'step_impact_title' => 'Informatie over Impactvol algoritme',
        'step_impact_info' => '
            <p class="text-sm text-gray-500">Beantwoord de toetsvragen met een "Ja" of "Nee", <span class="font-bold">Let op:</span> als alle drie de vragen met "Ja" zijn beantwoord, moet het algoritme als impactvol worden aangemerkt en opgenomen worden in het (rijksbrede) algoritmeregister.</p>',

        'step_validation_title' => 'Informatie over Validatie',
        'step_validation_info' => '
            <p class="text-sm text-gray-500">Antwoorden op de toetsvragen zijn gecontroleerd door product owner, doel is borging dat de classificatie is gevalideerd.</p>',

        'step_attachments_title' => 'Informatie over Documenten & Bijlagen',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">Als u documenten toevoegt, mag het ook om conceptversies gaan. Geef duidelijk aan wat voor soort document het is en of het concept of definitief is. Toevoegen, wijzigen of verwijderen van documenten vereist geen nieuwe versie van de verwerking. Voeg opmerkingen toe voor verduidelijking.</p>',
    ],

    'data_breach_record' => [
        'step_name_title' => 'Informatie over Naam Datalek',
        'step_name_info' => '
            <p class="text-sm text-gray-500">Vul hier de naam van het datalek in zoals bekend binnen jouw organisatie.</p>',

        'step_responsible_title' => 'Informatie over Verantwoordelijke',
        'step_responsible_info' => '
            <p class="text-sm text-gray-500">Volgens de AVG is de contactpersoon van de verwerkingsverantwoordelijke: “een natuurlijk persoon of rechtspersoon, een overheidsinstantie, een dienst of een ander orgaan die/dat, alleen of samen met anderen, het doel van en de middelen voor de verwerking van persoonsgegevens vaststelt” (art. 4 (7) AVG).</p>',
        'step_responsible_extra_info' => '
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Kies de contactpersoon van de verwerkingsverantwoordelijke uit de lijst</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Selecteer de contactpersoon van de verwerkingsverantwoordelijke uit de vooraf opgestelde lijst. Vul vervolgens de naam en contactgegevens in. Kies de functionaris voor gegevensbescherming (FG) uit dezelfde lijst. Vul het referentienummer uit het AVG-register in, indien beschikbaar. Dit veld is optioneel.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Sla de verwerkersovereenkomst op onder "Bijlagen".</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Voeg meerdere verwerkingsverantwoordelijken toe indien nodig, bijv. voor samenwerkingsverbanden. Klik op [+], selecteer de verwerkingsverantwoordelijke en herhaal. Voeg handmatig toe als niet in de lijst, contacteer de Lokaal Functioneel Beheerder voor hulp.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Vul alleen verwerkingsverantwoordelijken in waarvoor u persoonsgegevens verwerkt.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Algemene informatie over de verwerkingsverantwoordelijke</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">De verwerkingsverantwoordelijke kan een rechtspersoon, overheidsinstantie, dienst of ander orgaan zijn. Een partij is verwerkingsverantwoordelijke als deze het<span class="font-bold">doel</span> en de<span class="font-bold">middelen</span> voor de verwerking van persoonsgegevens bepaalt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een partij is verwerkingsverantwoordelijke wanneer deze het doel en de middelen voor de verwerking van persoonsgegevens bepaalt.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Een partij kan verwerkingsverantwoordelijke zijn, doordat:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Feitelijke omstandigheden (gedrag) hiertoe leiden. Hulpvragen zijn in dat geval: Waarom vindt deze verwerking plaats? Wie heeft dit geïnitieerd?</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1">Dit zo is vastgelegd in de wet, een uitspraak van een toezichthouder (zoals de Autoriteit Persoonsgegevens) of een contract</li>
            </ul>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Feitelijke omstandigheden (Gedrag) zijn belangrijker dan een contract bij bepaling van verwerkingsverantwoordelijkheid.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Verwerkersovereenkomst</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Tussen verwerkingsverantwoordelijke en verwerker moet een overeenkomst (art. 28(3) AVG) worden gesloten. Opslaan onder "Bijlagen" in dit AVG-register.</p>
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-gray-300 mb-1">Onderlinge verdeling verantwoordelijkheid</h3>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Bij meer dan één verwerkingsverantwoordelijke, vul éénmaal de onderlinge verdeling in.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4"><span class="font-bold">Let op:</span> Als verwerker heeft u hier geen invloed op en bent u niet verplicht dit in het register op te nemen. Voeg het toe als beschikbaar.</p>
            <p class="text-sm text-gray-950 dark:text-gray-300 mb-4">Qua verantwoordelijkheidsverdeling, zijn de volgende varianten mogelijk:</p>
            <ul class="list-disc list-outside mb-4 ml-5">
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Gezamenlijke verwerkingsverantwoordelijkheid:</span><br> Meerdere partijen stellen samen doel en middelen vast. Verplichte onderlinge regeling (artikel 26 AVG).</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Zelfstandige verwerkingsverantwoordelijkheid:</span><br> Hiervan is sprake als partijen samenwerken, maar zelf doel en middelen bepalen.</li>
                <li class="text-sm text-gray-950 dark:text-gray-300 mb-1"><span class="font-bold">Combinatie van beide:</span><br> Het kan zijn dat er bij één verwerking, zowel gezamenlijke als zelfstandige verantwoordelijkheid is.</li>
            </ul>',

        'step_dates_title' => 'Informatie over Data',
        'step_dates_info' => '
            <p class="text-sm text-gray-500">Vul bij "Datum ontdekking datalek" de datum in waarop het datalek werd ontdekt. Noteer bij "(Vermoedelijke) startdatum inbreuk" wanneer de inbreuk vermoedelijk begon en bij "Einddatum inbreuk" wanneer deze eindigde. Vermeld bij "Datum melding AP" de datum waarop het datalek aan de Autoriteit Persoonsgegevens is gemeld. Geef bij "Datum afronding" de datum waarop de afhandeling van het datalek is voltooid.</p>',

        'step_incident_title' => 'Informatie over Incident',
        'step_incident_info' => '
            <p class="text-sm text-gray-500">Beschrijf bij "Aard van incident" de aard van het datalek. Geef een korte "Samenvatting incident". Vermeld bij "Betrokken groep(en) personen" welke groepen personen betrokken zijn. Noteer de "Categorieën van persoonsgegevens" en de "Bijzondere categorieën van persoonsgegevens" die zijn gelekt. Geef een "Inschatting risico" van het incident. Beschrijf de "Maatregelen" die zijn genomen. Geef aan of het incident is "Gemeld aan betrokkene". Noteer het "Communicatiemiddel melding betrokkene". Vermeld ook of het incident is "Gemeld aan FG" (Functionaris Gegevensbescherming).</p>',

        'step_processing_records_title' => 'Informatie over Verwerkingen',
        'step_processing_records_info' => '
            <p class="text-sm text-gray-500">Koppel hier het datalek aan verwerkingen uit de drie verwerkingsregisters. In de tabellen onderaan deze pagina zijn de verwerkingen waar dit Datalek aan gekoppeld is terug te vinden en kun je ernaar navigeren.</p>',

        'step_attachments_title' => 'Informatie over Documenten & Bijlagen',
        'step_attachments_info' => '
            <p class="text-sm text-gray-500">Als u documenten toevoegt, mag het ook om conceptversies gaan. Geef duidelijk aan wat voor soort document het is en of het concept of definitief is. Toevoegen, wijzigen of verwijderen van documenten vereist geen nieuwe versie van de verwerking. Voeg opmerkingen toe voor verduidelijking.</p>',
    ],
];
