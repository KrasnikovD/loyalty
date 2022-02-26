define({ "api": [
  {
    "type": "post",
    "url": "/api/admin/login",
    "title": "Login",
    "name": "Login",
    "group": "AdminAuth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminAuth"
  },
  {
    "type": "post",
    "url": "/api/bill_programs/create",
    "title": "Create Bill Program",
    "name": "CreateBillProgram",
    "group": "AdminBillPrograms",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "field": "file_content",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "field": "file_content",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillPrograms"
  },
  {
    "type": "post",
    "url": "/api/bill_programs/list",
    "title": "Get Bill Programs List",
    "name": "GetBillProgramsList",
    "group": "AdminBillPrograms",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "post",
    "url": "/api/bill_types/list",
    "title": "Get Bill Type List",
    "name": "GetBillTypeList",
    "group": "AdminBillTypes",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminBillTypes"
  },
  {
    "type": "patch",
    "url": "/api/cards/attach_user/:number",
    "title": "Attach / Detach User To Card",
    "name": "AttachCardUser",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
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
    "type": "post",
    "url": "/api/cards/create",
    "title": "Create Card",
    "name": "CreateCard",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "optional": true,
            "field": "user_id",
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
            "field": "is_physical",
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
            "field": "is_main",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "patch",
    "url": "/api/bills/edit_value/:id",
    "title": "Edit Bill Value",
    "name": "EditBillValue",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "value",
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
    "type": "post",
    "url": "/api/cards/edit/:id",
    "title": "Edit Card",
    "name": "EditCard",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1"
            ],
            "optional": true,
            "field": "is_physical",
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
            "field": "is_main",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
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
    "url": "/api/cards/history/:id",
    "title": "Get Card History",
    "name": "GetCardHistory",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "post",
    "url": "/api/cards/list",
    "title": "Get Cards List",
    "name": "GetCardsList",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "filters",
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
            "field": "unattached",
            "description": "<p>only without users</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "patch",
    "url": "/api/cards/switch_status/:id",
    "title": "Switch Status",
    "name": "SwitchStatus",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCards"
  },
  {
    "type": "patch",
    "url": "/api/bills/up_card_program",
    "title": "Up Card Program",
    "name": "UpCardqProgram",
    "group": "AdminCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "value",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "number",
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
    "type": "post",
    "url": "/api/categories/create",
    "title": "Create Category",
    "name": "CreateCategory",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "file_content",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "post",
    "url": "/api/categories/sub_create",
    "title": "Create Sub Category",
    "name": "CreateSubCategory",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "parent_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "get",
    "url": "/api/categories/delete/:id",
    "title": "Delete Category",
    "name": "DeleteCategory",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "post",
    "url": "/api/categories/edit/:id",
    "title": "Edit Category",
    "name": "EditCategory",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "file_content",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "post",
    "url": "/api/categories/sub_edit/:id",
    "title": "Edit Sub Category",
    "name": "EditSubCategory",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "parent_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "get",
    "url": "/api/categories/list",
    "title": "Get Categories List",
    "name": "GetCategoriesList",
    "group": "AdminCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCategories"
  },
  {
    "type": "post",
    "url": "/api/coupons/create",
    "title": "Create Coupon",
    "name": "CreateCoupon",
    "group": "AdminCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "product_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "count",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "date_end",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCoupons"
  },
  {
    "type": "get",
    "url": "/api/coupons/delete/:id",
    "title": "Delete Coupon",
    "name": "DeleteCoupon",
    "group": "AdminCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCoupons"
  },
  {
    "type": "post",
    "url": "/api/coupons/edit/:id",
    "title": "Edit Coupon",
    "name": "EditCoupon",
    "group": "AdminCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "count",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "date_end",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCoupons"
  },
  {
    "type": "get",
    "url": "/api/coupons/get/:id",
    "title": "Get Coupon",
    "name": "GetCoupon",
    "group": "AdminCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCoupons"
  },
  {
    "type": "post",
    "url": "/api/coupons/list",
    "title": "Get Coupons",
    "name": "GetCoupons",
    "group": "AdminCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminCoupons"
  },
  {
    "type": "post",
    "url": "/api/devices/list",
    "title": "Get Devices",
    "name": "GetDevices",
    "group": "AdminDevices",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminDevices"
  },
  {
    "type": "post",
    "url": "/api/devices/send_pushes",
    "title": "Send Pushes",
    "name": "SendPushes",
    "group": "AdminDevices",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "all",
              "birthday"
            ],
            "optional": false,
            "field": "scope",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "body",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "fields",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminDevices"
  },
  {
    "type": "post",
    "url": "/api/fields/create",
    "title": "Create Field",
    "name": "CreateField",
    "group": "AdminFields",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "is_user_editable",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminFields"
  },
  {
    "type": "get",
    "url": "/api/fields/delete/:id",
    "title": "Delete Field",
    "name": "DeleteField",
    "group": "AdminFields",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminFields"
  },
  {
    "type": "post",
    "url": "/api/fields/edit/:id",
    "title": "Edit Field",
    "name": "EditField",
    "group": "AdminFields",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "is_user_editable",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminFields"
  },
  {
    "type": "get",
    "url": "/api/fields/get/:id",
    "title": "Get Field",
    "name": "GetField",
    "group": "AdminFields",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminFields"
  },
  {
    "type": "post",
    "url": "/api/fields/list",
    "title": "Get Fields List",
    "name": "GetFieldsList",
    "group": "AdminFields",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminFields"
  },
  {
    "type": "post",
    "url": "/api/news/create",
    "title": "Create News",
    "name": "CreateNews",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "file_content",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "sms",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "push",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "get",
    "url": "/api/news/delete/:id",
    "title": "Delete News",
    "name": "DeleteNews",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "post",
    "url": "/api/news/edit/:id",
    "title": "Edit News",
    "name": "EditNews",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "file_content",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "get",
    "url": "/api/news/get/:id",
    "title": "Get News",
    "name": "GetNews",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "post",
    "url": "/api/news/list",
    "title": "Get News List",
    "name": "GetNewsList",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "get",
    "url": "/api/news/moderate/:id",
    "title": "Set/Unset Visibility News",
    "name": "VisibilityNews",
    "group": "AdminNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminNews"
  },
  {
    "type": "post",
    "url": "/api/orders/add_basket/:sale_id",
    "title": "Add Basket",
    "name": "AddBasket",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "count",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "product_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "get",
    "url": "/api/orders/cancel/:id",
    "title": "Cancel Order",
    "name": "CancelOrder",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "post",
    "url": "/api/orders/create",
    "title": "Create Order",
    "name": "CreateOrder",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "outlet_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": false,
            "field": "products",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "get",
    "url": "/api/orders/delete_basket/:id",
    "title": "Delete Basket",
    "name": "DeleteBasket",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "get",
    "url": "/api/orders/delete/:id",
    "title": "Delete Order",
    "name": "DeleteOrder",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "post",
    "url": "/api/orders/edit_basket/:id",
    "title": "Edit Basket",
    "name": "EditBasket",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "count",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "post",
    "url": "/api/orders/edit/:id",
    "title": "Edit Order",
    "name": "EditOrder",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "outlet_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "products",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "get",
    "url": "/api/orders/get/:id",
    "title": "Get Order",
    "name": "GetOrder",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "post",
    "url": "/api/orders/list",
    "title": "Get Orders List",
    "name": "GetOrdersList",
    "group": "AdminOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "user_id",
            "description": "<p>user id</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "4",
              "5",
              "6",
              "7"
            ],
            "optional": true,
            "field": "status",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOrders"
  },
  {
    "type": "post",
    "url": "/api/outlets/create",
    "title": "Create Outlet",
    "name": "CreateOutlet",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
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
            "type": "string",
            "optional": false,
            "field": "lon",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "lat",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "to",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "get",
    "url": "/api/outlets/delete/:id",
    "title": "Delete Outlet",
    "name": "DeleteOutlet",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "post",
    "url": "/api/outlets/edit/:id",
    "title": "Edit Outlet",
    "name": "EditOutlet",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
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
            "type": "string",
            "optional": true,
            "field": "lon",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "lat",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "to",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "get",
    "url": "/api/outlets/get/:id",
    "title": "Get Outlet",
    "name": "GetOutlet",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "post",
    "url": "/api/outlets/list",
    "title": "Get Outlets List",
    "name": "GetOutletsList",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "post",
    "url": "/api/outlets/send_to_nearest/:id",
    "title": "Send To Nearest",
    "name": "SendToNearest",
    "group": "AdminOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "radius",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "body",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "fields",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminOutlets"
  },
  {
    "type": "post",
    "url": "/api/products/create",
    "title": "Create Product",
    "name": "CreateProduct",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "category_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "code",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "price",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "images",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "is_hit",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "is_novelty",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "is_by_weight",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "visible",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "get",
    "url": "/api/products/delete/:id",
    "title": "Delete Product",
    "name": "DeleteProduct",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "post",
    "url": "/api/products/edit/:id",
    "title": "Edit Product",
    "name": "EditProduct",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "category_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "code",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "price",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": true,
            "field": "images",
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
            "field": "is_hit",
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
            "field": "is_novelty",
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
            "field": "is_by_weight",
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
            "field": "visible",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "get",
    "url": "/api/products/get/:id",
    "title": "Get Product",
    "name": "GetProduct",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "post",
    "url": "/api/products/list",
    "title": "Get Products List",
    "name": "GetProductsList",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "category_id",
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
            "field": "hide_deleted",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "filters",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "patch",
    "url": "/api/products/set_position",
    "title": "Set positions",
    "name": "SetPositions",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer[]",
            "optional": false,
            "field": "ids_list",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "patch",
    "url": "/api/products/switch_visibility/:id",
    "title": "Switch Visibility",
    "name": "SwitchVisibility",
    "group": "AdminProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminProducts"
  },
  {
    "type": "post",
    "url": "/api/generate_report",
    "title": "Generate Report",
    "name": "GenerateReport",
    "group": "AdminReports",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer[]",
            "optional": true,
            "field": "program_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "start_dt",
            "description": "<p>Date in format Y-m-d (example: 2022-01-13)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "end_dt",
            "description": "<p>Date in format Y-m-d (example: 2022-01-13)</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminReports"
  },
  {
    "type": "get",
    "url": "/api/reviews/get/:id",
    "title": "Get Review",
    "name": "GetReview",
    "group": "AdminReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminReviews"
  },
  {
    "type": "get",
    "url": "/api/reviews/list",
    "title": "Get Reviews List",
    "name": "GetReviewsList",
    "group": "AdminReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "product",
              "outlet"
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
    "groupTitle": "AdminReviews"
  },
  {
    "type": "get",
    "url": "/api/reviews/moderate/:id",
    "title": "Set/Unset Visibility Review",
    "name": "VisibilityReview",
    "group": "AdminReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminReviews"
  },
  {
    "type": "get",
    "url": "/api/statistic/sales",
    "title": "Average Check",
    "name": "AverageCheck",
    "group": "AdminStat",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "outlet_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "date_from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "date_to",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/StatController.php",
    "groupTitle": "AdminStat"
  },
  {
    "type": "get",
    "url": "/api/statistic/product_rates",
    "title": "Product Rates",
    "name": "ProductRates",
    "group": "AdminStat",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "date_from",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "date_to",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/StatController.php",
    "groupTitle": "AdminStat"
  },
  {
    "type": "post",
    "url": "/api/stocks/create",
    "title": "Create Stock",
    "name": "CreateStock",
    "group": "AdminStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "file_content",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "sms",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "push",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminStocks"
  },
  {
    "type": "get",
    "url": "/api/stocks/delete/:id",
    "title": "Delete Stock",
    "name": "DeleteStock",
    "group": "AdminStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminStocks"
  },
  {
    "type": "post",
    "url": "/api/stocks/edit/:id",
    "title": "Edit News",
    "name": "EditStock",
    "group": "AdminStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "description",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "file_content",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminStocks"
  },
  {
    "type": "get",
    "url": "/api/stocks/get/:id",
    "title": "Get Stock",
    "name": "GetStock",
    "group": "AdminStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminStocks"
  },
  {
    "type": "post",
    "url": "/api/stocks/list",
    "title": "Get Stocks List",
    "name": "GetStocksList",
    "group": "AdminStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminStocks"
  },
  {
    "type": "get",
    "url": "/api/translations/rate_store",
    "title": "Get Store Rates Texts",
    "name": "GetStoreRatesTexts",
    "group": "AdminTranslations",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/TranslationController.php",
    "groupTitle": "AdminTranslations"
  },
  {
    "type": "patch",
    "url": "/api/translations/rate_store",
    "title": "Set Store Rates Texts",
    "name": "SetStoreRatesTexts",
    "group": "AdminTranslations",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "title",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "body",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/TranslationController.php",
    "groupTitle": "AdminTranslations"
  },
  {
    "type": "post",
    "url": "/api/users/create",
    "title": "Create User",
    "name": "CreateUser",
    "group": "AdminUsers",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "optional": true,
            "field": "third_name",
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
            "type": "datetime",
            "optional": true,
            "field": "birthday",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "archived",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "active",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": false,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "fields",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "code",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "field": "third_name",
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
            "type": "datetime",
            "optional": true,
            "field": "birthday",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "archived",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "boolean",
            "optional": true,
            "field": "active",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "fields",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "code",
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
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/AdminController.php",
    "groupTitle": "AdminUsers"
  },
  {
    "type": "post",
    "url": "/api/users/list",
    "title": "Get Users List",
    "name": "GetUsersList",
    "group": "AdminUsers",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "1"
            ],
            "optional": true,
            "field": "hide_deleted",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "filters",
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
    "type": "post",
    "url": "/api/clients/check_auth",
    "title": "Check Auth",
    "name": "CheckAuth",
    "group": "ClientAuth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "code",
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
            "type": "string",
            "optional": true,
            "field": "expo_token",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientAuth"
  },
  {
    "type": "post",
    "url": "/api/clients/login",
    "title": "Login",
    "name": "Login",
    "group": "ClientAuth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "code",
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
            "type": "string",
            "optional": true,
            "field": "expo_token",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientAuth"
  },
  {
    "type": "post",
    "url": "/api/clients/sms",
    "title": "Send Auth Sms",
    "name": "SendAuthSms",
    "group": "ClientAuth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientAuth"
  },
  {
    "type": "post",
    "url": "/api/clients/bill_programs/list",
    "title": "Get Bill Programs List",
    "name": "GetBillProgramsList",
    "group": "ClientBillPrograms",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientBillPrograms"
  },
  {
    "type": "post",
    "url": "/api/clients/bonus_history/get/:bill_id",
    "title": "Get Bonus History",
    "name": "GetBonusHistory",
    "group": "ClientBonusHistory",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientBonusHistory"
  },
  {
    "type": "get",
    "url": "/api/clients/cards/bind_card/:number",
    "title": "Bind Card",
    "name": "BindCard",
    "group": "ClientCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCards"
  },
  {
    "type": "post",
    "url": "/api/clients/cards/create",
    "title": "Create Card",
    "name": "CreateCard",
    "group": "ClientCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "number",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCards"
  },
  {
    "type": "post",
    "url": "/api/clients/cards/edit/:id",
    "title": "Edit Card",
    "name": "EditCard",
    "group": "ClientCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "number",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCards"
  },
  {
    "type": "post",
    "url": "/api/clients/cards/list",
    "title": "Get Cards List",
    "name": "GetCardsList",
    "group": "ClientCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCards"
  },
  {
    "type": "get",
    "url": "/api/clients/cards/set_main/:id",
    "title": "Set Main",
    "name": "SetMain",
    "group": "ClientCards",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCards"
  },
  {
    "type": "get",
    "url": "/api/clients/categories/list",
    "title": "Get Categories List",
    "name": "GetCategoriesList",
    "group": "ClientCategories",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCategories"
  },
  {
    "type": "get",
    "url": "/api/clients/coupons/get/:id",
    "title": "Get Coupon",
    "name": "GetCoupon",
    "group": "ClientCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCoupons"
  },
  {
    "type": "post",
    "url": "/api/clients/coupons/list",
    "title": "Get Coupons",
    "name": "GetCoupons",
    "group": "ClientCoupons",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientCoupons"
  },
  {
    "type": "post",
    "url": "/api/clients/send_support_email",
    "title": "Email Send",
    "name": "EmailSend",
    "group": "ClientEmail",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "text",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientEmail"
  },
  {
    "type": "post",
    "url": "/api/clients/favorites/add",
    "title": "Add Favorite",
    "name": "AddFavorite",
    "group": "ClientFavorites",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "product_id",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientFavorites"
  },
  {
    "type": "get",
    "url": "/api/clients/favorites/delete/:product_id",
    "title": "Delete Favorite",
    "name": "DeleteFavorite",
    "group": "ClientFavorites",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientFavorites"
  },
  {
    "type": "post",
    "url": "/api/clients/favorites/list",
    "title": "Get Favorite Products List",
    "name": "GetFavoriteProductsList",
    "group": "ClientFavorites",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientFavorites"
  },
  {
    "type": "post",
    "url": "/api/clients/device_init",
    "title": "Device Init",
    "name": "DeviceInit",
    "group": "ClientInit",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "expo_token",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientInit"
  },
  {
    "type": "post",
    "url": "/api/clients/set_location",
    "title": "Set Location",
    "name": "SetLocation",
    "group": "ClientLocation",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "lon",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "lat",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientLocation"
  },
  {
    "type": "get",
    "url": "/api/clients/news/get/:id",
    "title": "Get News",
    "name": "GetNews",
    "group": "ClientNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientNews"
  },
  {
    "type": "post",
    "url": "/api/clients/news/list",
    "title": "Get News List",
    "name": "GetNewsList",
    "group": "ClientNews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientNews"
  },
  {
    "type": "get",
    "url": "/api/clients/orders/cancel/:id",
    "title": "Cancel Order",
    "name": "CancelOrder",
    "group": "ClientOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientOrders"
  },
  {
    "type": "post",
    "url": "/api/clients/orders/create",
    "title": "Create Order",
    "name": "CreateOrder",
    "group": "ClientOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "outlet_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": false,
            "field": "products",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "comment",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientOrders"
  },
  {
    "type": "get",
    "url": "/api/clients/orders/get/:id",
    "title": "Get Order",
    "name": "GetOrder",
    "group": "ClientOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientOrders"
  },
  {
    "type": "post",
    "url": "/api/clients/orders/list",
    "title": "Get Orders List",
    "name": "GetOrdersList",
    "group": "ClientOrders",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "0",
              "4",
              "5",
              "6",
              "7"
            ],
            "optional": true,
            "field": "status",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientOrders"
  },
  {
    "type": "post",
    "url": "/api/clients/outlets/list",
    "title": "Get Outlets List",
    "name": "GetOutletsList",
    "group": "ClientOutlets",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "lon",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "lat",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientOutlets"
  },
  {
    "type": "get",
    "url": "/api/clients/products/get/:id",
    "title": "Get Product",
    "name": "GetProduct",
    "group": "ClientProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientProducts"
  },
  {
    "type": "post",
    "url": "/api/clients/products/list",
    "title": "Get Products List",
    "name": "GetProductsList",
    "group": "ClientProducts",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "integer[]",
            "optional": true,
            "field": "category_ids",
            "description": "<p>Array of category ids</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "is_hit",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "is_novelty",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "min_price",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "max_price",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientProducts"
  },
  {
    "type": "post",
    "url": "/api/clients/profile/edit",
    "title": "Edit Profile",
    "name": "EditProfile",
    "group": "ClientProfile",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
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
            "type": "datetime",
            "optional": true,
            "field": "birthday",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "fields",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientProfile"
  },
  {
    "type": "get",
    "url": "/api/clients/profile",
    "title": "Get Profile",
    "name": "GetProfile",
    "group": "ClientProfile",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientProfile"
  },
  {
    "type": "post",
    "url": "/api/clients/reviews/create",
    "title": "Create Review",
    "name": "CreateReview",
    "group": "ClientReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "object_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "product",
              "outlet"
            ],
            "optional": false,
            "field": "type",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "rating",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientReviews"
  },
  {
    "type": "get",
    "url": "/api/clients/reviews/delete/:id",
    "title": "Delete Review",
    "name": "DeleteReview",
    "group": "ClientReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientReviews"
  },
  {
    "type": "get",
    "url": "/api/clients/reviews/get/:id",
    "title": "Get Review",
    "name": "GetReview",
    "group": "ClientReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientReviews"
  },
  {
    "type": "post",
    "url": "/api/clients/reviews/list",
    "title": "Get Reviews List",
    "name": "GetReviewsList",
    "group": "ClientReviews",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "product",
              "outlet"
            ],
            "optional": true,
            "field": "type",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientReviews"
  },
  {
    "type": "get",
    "url": "/api/clients/stocks/get/:id",
    "title": "Get Stock",
    "name": "GetStock",
    "group": "ClientStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientStocks"
  },
  {
    "type": "post",
    "url": "/api/clients/stocks/list",
    "title": "Get Stocks List",
    "name": "GetStocksList",
    "group": "ClientStocks",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Basic current user token</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/ClientController.php",
    "groupTitle": "ClientStocks"
  },
  {
    "type": "get",
    "url": "/api/outlets/card/get/:card_number",
    "title": "Get Card",
    "name": "GetCard",
    "group": "OutletCards",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletCards"
  },
  {
    "type": "get",
    "url": "/api/outlets/coupon/get/:id",
    "title": "Get Coupon",
    "name": "GetCoupon",
    "group": "OutletCoupons",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletCoupons"
  },
  {
    "type": "get",
    "url": "/api/outlets/sales/cancel/:sale_id",
    "title": "Cancel Sale",
    "name": "CancelSale",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "post",
    "url": "/api/outlets/sales/create",
    "title": "Create Sale",
    "name": "CreateSale",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "integer",
            "optional": false,
            "field": "outlet_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "card_number",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "debited",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": false,
            "field": "products",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "integer",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": true,
            "field": "cert",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "post",
    "url": "/api/outlets/sales/edit/:sale_id",
    "title": "Edit Sale",
    "name": "EditSale",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "card_number",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "debited",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "object[]",
            "optional": true,
            "field": "products",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "post",
    "url": "/api/outlets/users/find_by_phone",
    "title": "Find User By Phone",
    "name": "FindUserByPhone",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "get",
    "url": "/api/outlets/sales/get/:sale_id",
    "title": "Get Sale",
    "name": "GetSale",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "post",
    "url": "/api/outlets/users/list",
    "title": "Get Users List",
    "name": "GetUsersList",
    "group": "OutletSales",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "like",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "order",
            "description": "<p>order field name</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "dir",
            "description": "<p>order direction</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "offset",
            "description": "<p>start row number, used only when limit is set</p>"
          },
          {
            "group": "Parameter",
            "type": "integer",
            "optional": true,
            "field": "limit",
            "description": "<p>row count</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "xml",
              "json"
            ],
            "optional": true,
            "field": "out_format",
            "description": ""
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/OutletController.php",
    "groupTitle": "OutletSales"
  },
  {
    "type": "post",
    "url": "/api/test",
    "title": "Test",
    "name": "Test",
    "group": "TestApi",
    "version": "0.0.0",
    "filename": "../app/Http/Controllers/Api/TestController.php",
    "groupTitle": "TestApi"
  }
] });
