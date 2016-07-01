To add mobile versions of your current theme while using this module
just add "mobile" and "tablet" directories to your theme with all the
standard files a theme needs to work for jaris but adjusted to work
for those devices. For example:

themes/mytheme/mobile
->page.php
->content.php
->block.php
->block-content.php
->user-profile.php
->info.php
->style.css
->preview.png
->images

themes/mytheme/tablet
->page.php
->content.php
->block.php
->block-content.php
->user-profile.php
->info.php
->style.css
->preview.png
->images

So in the end you will work the mobile and tablet themes as you usually do
with a normal theme. A metatag to make the design stretch to take the
whole mobile browser as an application is:

<meta name="viewport" content="width=###px">

or much better

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">