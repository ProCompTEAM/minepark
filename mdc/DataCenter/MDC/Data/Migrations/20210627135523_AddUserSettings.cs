using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;
using System;

namespace MDC.Data.Migrations
{
    public partial class AddUserSettings : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AlterColumn<string>(
                name: "Email",
                table: "Users",
                type: "nvarchar(128)",
                nullable: true,
                oldClrType: typeof(string),
                oldType: "nvarchar(4096)",
                oldNullable: true);

            migrationBuilder.CreateTable(
                name: "UserSettings",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    UnitId = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Licenses = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    Attributes = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    Organisation = table.Column<int>(nullable: false),
                    World = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    X = table.Column<double>(nullable: false),
                    Y = table.Column<double>(nullable: false),
                    Z = table.Column<double>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_UserSettings", x => x.Id);
                });
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            throw new NotImplementedException();
        }
    }
}
