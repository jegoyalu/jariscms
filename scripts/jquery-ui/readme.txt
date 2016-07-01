jquery.ui.js
=============================

Version: 1.9.0
Date Build: 10/10/2012
Includes: All elements

jquery.ui.touch-punch.min.js
=============================

Source: http://touchpunch.furf.com/

Binds touch events to mouse events in order
for correct functioning of jquery ui on mobile devices.

Using Touch Punch
=============================

Just follow these simple steps to enable touch events in your jQuery UI app:

    1. Include jQuery and jQuery UI on your page.

    <script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
    <script src="http://code.jquery.com/ui/1.8.21/jquery-ui.min.js"></script>

    2. Include Touch Punch after jQuery UI and before its first use.

    Please note that if you are using jQuery UI's components, Touch Punch must be included after jquery.ui.mouse.js, as Touch Punch modifies its behavior.

    <script src="jquery.ui.touch-punch.min.js"></script>

