define({ "api": [
  {
    "type": "post",
    "url": "/api/bill_programs/create",
    "title": "Create Bill Program",
    "name": "CreateBillProgram",
    "group": "AdminBillPrograms",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "to",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "percent",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "bill_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "get",
    "url": "/api/bill_programs/delete/:id",
    "title": "Delete Bill Program",
    "name": "DeleteBillProgram",
    "group": "AdminBillPrograms",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "post",
    "url": "/api/bill_programs/edit/:id",
    "title": "Edit Bill Program",
    "name": "EditBillProgram",
    "group": "AdminBillPrograms",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "to",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "percent",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "bill_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "get",
    "url": "/api/bill_programs/get/:id",
    "title": "Get Bill Program",
    "name": "GetBillProgram",
    "group": "AdminBillPrograms",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "get",
    "url": "/api/bill_programs/list",
    "title": "Get Bill Programs List",
    "name": "GetBillProgramsList",
    "group": "AdminBillPrograms",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "get",
    "url": "/api/bill_programs/list/:bill_id",
    "title": "Get Bill Programs List for Bill",
    "name": "GetBillProgramsListForBill",
    "group": "AdminBillPrograms",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "post",
    "url": "/api/bill_types/create",
    "title": "Create Bill Type",
    "name": "CreateBillType",
    "group": "AdminBillTypes",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "get",
    "url": "/api/bill_types/delete/:id",
    "title": "Delete Bill Type",
    "name": "DeleteBillType",
    "group": "AdminBillTypes",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "post",
    "url": "/api/bill_types/edit/:id",
    "title": "Edit Bill Type",
    "name": "EditBillType",
    "group": "AdminBillTypes",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "get",
    "url": "/api/bill_types/get/:id",
    "title": "Get Bill Type",
    "name": "GetBillType",
    "group": "AdminBillTypes",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "get",
    "url": "/api/bill_types/list",
    "title": "Get Bill Type List",
    "name": "GetBillTypeList",
    "group": "AdminBillTypes",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "post",
    "url": "/api/cards/create",
    "title": "Create Card",
    "name": "CreateCard",
    "group": "AdminCards",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "number",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "user_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "get",
    "url": "/api/cards/delete/:id",
    "title": "Delete Card",
    "name": "DeleteCard",
    "group": "AdminCards",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "post",
    "url": "/api/cards/edit/:id",
    "title": "Edit Card",
    "name": "EditCard",
    "group": "AdminCards",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "number",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "user_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "get",
    "url": "/api/cards/get/:id",
    "title": "Get Card",
    "name": "GetCard",
    "group": "AdminCards",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "get",
    "url": "/api/cards/list",
    "title": "Get Cards List",
    "name": "GetCardsList",
    "group": "AdminCards",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "post",
    "url": "/api/users/create",
    "title": "Create User",
    "name": "CreateUser",
    "group": "AdminUsers",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "first_name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "second_name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1"
            ],
            "optional": false,
            "field": "type",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  },
  {
    "type": "get",
    "url": "/api/users/delete/:id",
    "title": "Delete User",
    "name": "DeleteUser",
    "group": "AdminUsers",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  },
  {
    "type": "post",
    "url": "/api/users/edit/:id",
    "title": "Edit User",
    "name": "EditUser",
    "group": "AdminUsers",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "first_name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "second_name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "password",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1"
            ],
            "optional": true,
            "field": "type",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  },
  {
    "type": "get",
    "url": "/api/users/get/:id",
    "title": "Get User",
    "name": "GetUser",
    "group": "AdminUsers",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  },
  {
    "type": "get",
    "url": "/api/users/list",
    "title": "Get Users List",
    "name": "GetUsersList",
    "group": "AdminUsers",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  }
] });
