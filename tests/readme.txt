Testing
=================

JarisCMS was developed by a single individual who was kind of a
novice programmer at the time. Been developed by only one developer
with many things still to learn, a testing framework wasn't even
thought of. Years later after reading so many articles on the net
that focused on testing frameworks and such, JarisCMS core developer
thought that none of this would fit well for JarisCMS due to the
messy (nooby) nature the code was written in and also PHP 4 still lived on.
Been this the case it was decided to develop some simple scripts to
execute every core page and output any execution errors found.


How to test
=================

In order to run the JarisCMS test scripts from the root jariscms
directory execute:

    php tests/run.php

This script will output any errors encountered when running the tests
for you to inspect and fix.
