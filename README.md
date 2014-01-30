SQLICostumRESTApi
============

SQLICostumRESTApi is an ezpublish extension that helps you filtring list of children nodes  by class identifier

how to :
install the extension by copying it into the extension folder
enable the extension by adding the following to "ActiveExtensions" array in the `settings/override/site.ini.append.php` 
```
ActiveExtensions[]=SQLICostumRESTApi
```
easy to use : 
- call the sqliRest provider instead of the default one : `/api/sqliRest/....` instead of (`/api/ezp/`)

- just add the prefix /class/&lt;your class identifier&gt; to the `/content/node/(nodeId)/list` REST URI


uri example :
```
www.example.com/index_rest.php/api/sqliRest/content/node/2/list/class/article
```
