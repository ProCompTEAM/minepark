using Microsoft.EntityFrameworkCore.Migrations;

namespace MDC.Data.Migrations
{
    public partial class RemoveUserPositionRows : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "Level",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "X",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "Y",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "Z",
                table: "Users");
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "Level",
                table: "Users",
                type: "nvarchar(128)",
                nullable: false,
                defaultValue: "");

            migrationBuilder.AddColumn<double>(
                name: "X",
                table: "Users",
                type: "double",
                nullable: false,
                defaultValue: 0.0);

            migrationBuilder.AddColumn<double>(
                name: "Y",
                table: "Users",
                type: "double",
                nullable: false,
                defaultValue: 0.0);

            migrationBuilder.AddColumn<double>(
                name: "Z",
                table: "Users",
                type: "double",
                nullable: false,
                defaultValue: 0.0);
        }
    }
}
