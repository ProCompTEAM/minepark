using System;
using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;

namespace MDC.Data.Migrations
{
    public partial class AddPhoneLogic : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "Phones",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Subject = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Number = table.Column<long>(nullable: false),
                    SubjectType = table.Column<int>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false),
                    UpdatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_Phones", x => x.Id);
                });

            migrationBuilder.Sql(@"
                    INSERT INTO phones (Subject, Number, SubjectType, CreatedDate, UpdatedDate)  
	                    VALUES(
	                        (SELECT Name FROM users AS Subject),
	                        (SELECT Id FROM users AS NUMBER) + 10000,
	                        1,
	                        CURRENT_DATE(),
	                        CURRENT_DATE()
                    )");
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "Phones");
        }
    }
}
