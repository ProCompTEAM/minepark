using Microsoft.EntityFrameworkCore.Migrations;
using System;

namespace MDC.Data.Migrations
{
    public partial class UpdateModelProperties : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "Attributes",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "Licenses",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "Organisation",
                table: "Users");

            //fix https://github.com/dotnet/efcore/issues/21656 problem
            migrationBuilder.Sql("ALTER TABLE MapPoints CHANGE Level World nvarchar(128);");
            migrationBuilder.Sql("ALTER TABLE FloatingTexts CHANGE Level World nvarchar(128);");

            migrationBuilder.AddColumn<string>(
                name: "Email",
                table: "Users",
                type: "nvarchar(128)",
                nullable: true);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            throw new NotImplementedException();
        }
    }
}
