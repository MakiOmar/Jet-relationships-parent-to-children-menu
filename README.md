
# Jet relationships parent to children menu

This Code works with [Jet engine](https://crocoblock.com/plugins/jetengine/) relationships
## Scenario:
- Created post type `book`
- Created post type `book_content`. Shall be used as (Page, section or chapter)
- Created one to many relationship ( book to book contnet ). Check `{parent_child_rel_id}` in the code. 
- Created one to many relationship ( book contnet to book contnet ). Check `{child_child_rel_id}` in the code. 

> **Note**
> This code works if each relationship has it's own table. [Documentation](https://crocoblock.com/knowledge-base/articles/jetengine-how-to-create-relationships-between-posts/)

> **warning**
> **If you are not using separate table**: Please make sure to set third parameter of function `anony_query_related_children` call to `true`