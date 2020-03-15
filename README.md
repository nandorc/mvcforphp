# mvcforphp
Template files to apply mvc pattern with native php
By Daniel F. Rivera C.

In order to use this library your web project must look like this

- rootFolder
  - controllers
    - [EachNewControllerHere...]
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
      - mvcforphp.php
      - [EachPersonalCSSLibsHere...]
      - [EachPersonalJSLibsHere...]
    - videos
      - [VideoFilesHere...]
  - views
    - shared
      - components (optional)
        - [EachNewComponentHere...]
      - template.php
    - [EachNewViewHere...]
  - .htaccess
  - index.php
    
It's important to keep structure like this to get better performance. Also you have to keep in mind the next advices:
- Controllers must be named with the Controller prefix. Example: userController.php
- Views must be named with the View prefix. Example: homeView.php
    
