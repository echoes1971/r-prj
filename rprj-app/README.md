
# Deploy

On windows
```
cd rprj-app
del build
npm run winbuild
move .\build\ app
Compress-Archive -Path .\app -DestinationPath .\app.zip
del app
```

Copy app.zip on the server and unzip it in the root directory of your site.


See: https://wordpress.org/support/topic/yarn-build-not-recognizing-public_url/



# TODO

- App.js
  - Finish the Create /c/-10/FNote

- comp.fform.js
  - FField
  -  FFileField - Render OK-ish. TODO: the backend.
  -  FList
  -      FChildSort - TODO
  -  FCheckBox - TODO

# DONE

- comp.fform.js
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
