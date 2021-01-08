# mvcforphp
Template files to apply mvc pattern with native php
By Daniel F. Rivera C.

In order to use this library your web project must look like this

- rootFolder
  - .vscode
    - sftp.json
  - controllers
    - [EachNewControllerHere...]
  - documentation
    - [DBStructureFiles...]
    - [MockUps...]
  - mailing
    - [MailingTemplateFiles...]
  - models
    - [EachNewModelHere...]
  - resources
    - audios
      - [AudioFilesHere...]
    - docs
      - [DocumentFilesHere...]
    - images
      - icon.ico (optional)
      - [AdditionalImagesHere...]
    - scripts
      - mvc4php
        - dbconf.json (optional)
        - globals4app.php
        - globals4controllers.php
        - globals4views.php
      - [EachPersonalCSSLibsHere...]
      - [EachPersonalJSLibsHere...]
    - videos
      - [VideoFilesHere...]
  - vendor
    - [ComposerFilesAndFolders...]
  - views
    - shared
      - components (optional)
        - [EachNewComponentHere...]
      - template.php
    - [EachNewViewHere...]
  - .gitignore
  - .htaccess
  - composer.json
  - composer.lock
  - index.php
  - LICENSE
  - README.md
    
It's important to keep structure like this to get better performance. Also you have to keep in mind the next advices:
- Controllers must be named with the Controller prefix. Example: userController.php
- Views must be named with the View prefix. Example: homeView.php
    
