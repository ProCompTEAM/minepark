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
                name: "BanRecordId",
                table: "Users",
                nullable: true);

            migrationBuilder.CreateTable(
                name: "UserBanRecords",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    UserName = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    IssuerName = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    ReleaseDate = table.Column<DateTime>(nullable: false),
                    Reason = table.Column<string>(type: "nvarchar(128)", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_UserBanRecords", x => x.Id);
                });

            migrationBuilder.CreateIndex(
                name: "IX_Users_BanRecordId",
                table: "Users",
                column: "BanRecordId");

            migrationBuilder.AddForeignKey(
                name: "FK_Users_UserBanRecords_BanRecordId",
                table: "Users",
                column: "BanRecordId",
                principalTable: "UserBanRecords",
                principalColumn: "Id",
                onDelete: ReferentialAction.Restrict);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            throw new NotImplementedException();
        }
    }
}
