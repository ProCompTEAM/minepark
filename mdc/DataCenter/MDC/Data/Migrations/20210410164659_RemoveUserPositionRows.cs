using Microsoft.EntityFrameworkCore.Migrations;
using System;

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
            throw new NotImplementedException();
        }
    }
}
