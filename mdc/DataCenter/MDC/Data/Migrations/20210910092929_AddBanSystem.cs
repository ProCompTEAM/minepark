using System;
using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;

namespace MDC.Data.Migrations
{
    public partial class AddBanSystem : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "BanId",
                table: "Users",
                nullable: true);

            migrationBuilder.CreateTable(
                name: "PlayerBans",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    UserName = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Issuer = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    End = table.Column<DateTime>(nullable: false),
                    Reason = table.Column<string>(type: "nvarchar(128)", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_PlayerBans", x => x.Id);
                });

            migrationBuilder.CreateIndex(
                name: "IX_Users_BanId",
                table: "Users",
                column: "BanId");

            migrationBuilder.AddForeignKey(
                name: "FK_Users_PlayerBans_BanId",
                table: "Users",
                column: "BanId",
                principalTable: "PlayerBans",
                principalColumn: "Id",
                onDelete: ReferentialAction.Restrict);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Users_PlayerBans_BanId",
                table: "Users");

            migrationBuilder.DropTable(
                name: "PlayerBans");

            migrationBuilder.DropIndex(
                name: "IX_Users_BanId",
                table: "Users");

            migrationBuilder.DropColumn(
                name: "BanId",
                table: "Users");
        }
    }
}
