using System;
using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;

namespace MDC.Data.Migrations
{
    public partial class AddFloatingTexts : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<double>(
                name: "Balance",
                table: "Phones",
                nullable: false,
                defaultValue: 0.0);

            migrationBuilder.CreateTable(
                name: "FloatingTexts",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Text = table.Column<string>(type: "nvarchar(4096)", nullable: false),
                    UnitId = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Level = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    X = table.Column<double>(nullable: false),
                    Y = table.Column<double>(nullable: false),
                    Z = table.Column<double>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_FloatingTexts", x => x.Id);
                });
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "FloatingTexts");

            migrationBuilder.DropColumn(
                name: "Balance",
                table: "Phones");
        }
    }
}
