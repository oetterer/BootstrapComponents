<!-- Begin of generated contents by readmeContentsBuilder.php -->

## TestCases

Contains 18 files with a total of 87 tests:
* [accordion-01.json](TestCases/accordion-01.json) Test accordion component
* [alert-01.json](TestCases/alert-01.json) Test alert component
* [badge-01.json](TestCases/badge-01.json) Test badge component
* [button-01.json](TestCases/button-01.json) Test button component
* [carousel-01.json](TestCases/carousel-01.json) Test carousel component
* [collapse-01.json](TestCases/collapse-01.json) Test collapse component
* [gallery_carousel-01.json](TestCases/gallery_carousel-01.json) Test adding mode carousel to gallery tag
* [icon-01.json](TestCases/icon-01.json) Test icon component
* [image_modal-01.json](TestCases/image_modal-01.json) Test replacing default image tags with modals
* [image_modal-02.json](TestCases/image_modal-02.json) Test image modals with invalid thumb image
* [image_modal-03.json](TestCases/image_modal-03.json) Test image modals with suppression via magic word
* [jumbotron-01.json](TestCases/jumbotron-01.json) Test jumbotron component
* [label-01.json](TestCases/label-01.json) Test label component
* [modal-01.json](TestCases/modal-01.json) Test modal component
* [panel-01.json](TestCases/panel-01.json) Test panel component
* [popover-01.json](TestCases/popover-01.json) Test popover component
* [tooltip-01.json](TestCases/tooltip-01.json) Test tooltip component
* [well-01.json](TestCases/well-01.json) Test well component

-- Last updated on 2018-01-29 by `readmeContentsBuilder.php`

<!-- End of generated contents by readmeContentsBuilder.php -->

## Writing a test case

### Assertions

Integration tests aim to prove that the "integration" between MediaWiki
and the extension works at a sufficient level therefore assertion
may only check or verify a specific part of an output to avoid that
system information (DB ID, article url etc.) distort to overall test results.

### Add a new test case

- Follow the `alert-01.json` example on how to structure the JSON file (setup,
  test etc.)
- You can find an example for image upload in `carousel-01.json`.
- You can add templates the same way as the Target page in `button-01.json`.
  Just provide an appropriate content.
