Reference Module Template (Clean Architecture)

How to create a new dictionary (typed storage, hierarchical with parent_id):

1) Copy this folder to a new location and rename occurrences of Country to YourType
   - Example: Country -> City

2) Create SQL table from migration.sql (rename table ref_country -> ref_city)

3) Move stubs into src structure and replace placeholders:
   - <Type>  -> Country (or your type)
   - <type>  -> country
   - <Table> -> ref_country

4) Wire up in public_html/index.php (repositories, use cases, controller) and add routes.

Features:
 - Hierarchical structure via parent_uuid (Adjacency List pattern)
 - Two node types: catalog (folder, is_catalog=true) and object (item, is_catalog=false)
 - Sort order support (sort_order field)
 - Filtering by parent_uuid and/or is_catalog in list queries

Files in this template:
 - Entity.stub                (Domain entity with parentUuid, isCatalog bool, sortOrder)
 - RepositoryInterface.stub   (Domain repository contract)
 - RepositoryMysql.stub       (Infrastructure implementation)
 - UseCases.stub              (Application use cases: list/create/update/delete)
 - Controller.stub            (HTTP controller with CRUD)
 - routes.snippet.stub        (Routes snippet)
 - migration.sql              (DB schema with parent_uuid, is_catalog TINYINT(1))

API Usage:
 - GET /reference/<type>?parent_uuid=xxx&is_catalog=true
 - POST /reference/<type> with body: {"code":"...","name":"...","is_catalog":true,"parent_uuid":"...","sort_order":0}
 - PUT /reference/<type>/{uuid} - update including moving to another parent
 - DELETE /reference/<type>/{uuid}


