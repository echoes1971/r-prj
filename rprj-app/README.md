
# Deploy

On windows
```
cd rprj-app
del build

npm run winbuild
# IF you want to set a title
($env:REACT_APP_SITE_TITLE='MyPrj') -and (npm run winbuild)

move .\build\ app
Compress-Archive -Path .\app -DestinationPath .\app.zip
del app
```

Copy app.zip on the server and unzip it in the root directory of your site.


See: https://wordpress.org/support/topic/yarn-build-not-recognizing-public_url/



# TODO

- App.js
  - Manage -> Deleted objects: show the list of deleted objects, so to inspect them or revert deletion

- comp.fform.js
  - Button Reload
  - FField
  -  FFileField - Render OK-ish. TODO: the backend.
  -  FList
  -      FChildSort - TODO
  -  FCheckBox - TODO

# DONE

- App.js

- comp.fform.js
  - Button Close: open the parent folder, if exist.
  - show ONLY SAVE button while creating a new object /c/sdfs/DBENote
  - Delete
  - Save
  - FField
  -  FNumber
  -      FPercent
  -  FString
  -      FLanguage
  -      FUuid
  -      FPassword
  -      FPermissions
  -  FFileField - Render OK-ish.
  -  FList
  -  FTextArea
  -      FHtml
  -  FDateTime
  -      FDateTimeReadOnly
  -  FKField
  -      FKObjectField






# ???


npm install axios --save


Emojis:
- https://unicode.org/emoji/charts/full-emoji-list.html
- https://www.quackit.com/character_sets/emoji/emoji_v3.0/unicode_emoji_v3.0_characters_all.cfm
