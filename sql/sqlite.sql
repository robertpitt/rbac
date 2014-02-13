PRAGMA synchronous = OFF;
PRAGMA journal_mode = MEMORY;
BEGIN TRANSACTION;
CREATE TABLE "rbac_permissions" (
  "id" int(11) NOT NULL PRIMARY KEY,
  "left" int(11) NOT NULL,
  "right" int(11) NOT NULL,
  "title" char(64) NOT NULL,
  "description" text NOT NULL
);
INSERT INTO "rbac_permissions" VALUES (1,1,2,'root','Root Entity');
CREATE TABLE "rbac_role_permissions" (
  "role_id" int(11) NOT NULL,
  "permission_id" int(11) NOT NULL,
  PRIMARY KEY ("role_id","permission_id")
  CONSTRAINT "fk_rbac_role_permissions_1" FOREIGN KEY ("role_id") REFERENCES "rbac_roles" ("id") ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT "fk_rbac_role_permissions_2" FOREIGN KEY ("permission_id") REFERENCES "rbac_permissions" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);
CREATE TABLE "rbac_roles" (
  "id" int(11) PRIMARY KEY,
  "left" int(11) NOT NULL,
  "right" int(11) NOT NULL,
  "title" varchar(128) NOT NULL,
  "description" text NOT NULL
);
INSERT INTO "rbac_roles" VALUES (1,1,2,'root','Root Entity');
CREATE TABLE "rbac_user_roles" (
  "account_id" int(11) NOT NULL,
  "role_id" int(11) NOT NULL,
  PRIMARY KEY ("account_id","role_id")
  CONSTRAINT "fk_rbac_user_roles_1" FOREIGN KEY ("role_id") REFERENCES "rbac_roles" ("id") ON DELETE CASCADE ON UPDATE NO ACTION
);
CREATE INDEX "rbac_role_permissions_fk_rbac_role_permissions_1" ON "rbac_role_permissions" ("role_id");
CREATE INDEX "rbac_role_permissions_fk_rbac_role_permissions_2" ON "rbac_role_permissions" ("permission_id");
CREATE INDEX "rbac_permissions_title" ON "rbac_permissions" ("title");
CREATE INDEX "rbac_permissions_left" ON "rbac_permissions" ("left");
CREATE INDEX "rbac_permissions_right" ON "rbac_permissions" ("right");
CREATE INDEX "rbac_user_roles_fk_rbac_user_roles_1" ON "rbac_user_roles" ("role_id");
CREATE INDEX "rbac_roles_title" ON "rbac_roles" ("title");
CREATE INDEX "rbac_roles_left" ON "rbac_roles" ("left");
CREATE INDEX "rbac_roles_right" ON "rbac_roles" ("right");
END TRANSACTION;