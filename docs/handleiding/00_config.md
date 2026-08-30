---
title: "OpenVWR: Handleiding"
author: [OpenVWR]
keywords: [OpenVWR, Verwerkingsregister, AVG, Handleiding]
lang: nl
papersize: "A4"
titlepage-rule-color: "F84F39"
titlepage: true
toc-own-page: true
toc: true
footnotes-pretty: true
header-includes:
  - |
    ```{=latex}
    % Houd kopjes bij hun tekst: een kopje begint alleen als er onder aan de
    % pagina nog genoeg ruimte is voor het kopje plus een paar regels tekst.
    % Anders schuift het kopje door naar de volgende pagina.
    \usepackage{needspace}
    \let\ovwroldsubsection\subsection
    \renewcommand\subsection{\needspace{6\baselineskip}\ovwroldsubsection}
    \let\ovwroldsubsubsection\subsubsection
    \renewcommand\subsubsection{\needspace{5\baselineskip}\ovwroldsubsubsection}
    % Laat geen losse begin- of eindregel van een alinea achter op een pagina.
    \widowpenalty=10000
    \clubpenalty=10000
    \displaywidowpenalty=10000
    ```
...

<!--
OpenVWR huisstijl: accent #F84F39 (zie FilamentServiceProvider en openvwr.nl).
De overige kleuren zijn statuskleuren, gebruikt in 03_goedkeuringsproces.
-->
\definecolor{accent}{RGB}{248, 79, 57}
\definecolor{blue}{RGB}{37, 99, 235}
\definecolor{orange}{RGB}{217, 119, 6}
\definecolor{green}{RGB}{22, 163, 74}
\definecolor{gray}{RGB}{82, 82, 91}
\let\oldtextbf\textbf
\renewcommand\textbf[1]{{\color{accent}\oldtextbf{#1}}}
\let\oldsection\section
\renewcommand\section[1]{{\newpage\oldsection{#1}}}

\mbox{}
\vfill

**OpenVWR: Handleiding**

Het centrale platform voor al uw privacyverwerkingen.

Website: [https://openvwr.nl/](https://openvwr.nl/) \
Document: Pandoc, Eisvogel - https://github.com/Wandmalfarbe/pandoc-latex-template

\textcopyright Sourcehaven BV

OpenVWR is een fork van het oorspronkelijk door iRealisatie - Ministerie van Volksgezondheid, Welzijn en Sport ontwikkelde verwerkingsregister.

Licentie: CC0-1.0

Bij het citeren van de inhoud mag niet de indruk gewekt worden dat de oorspronkelijke auteurs zonder meer de strekking van het afgeleide werk onderschrijven.
