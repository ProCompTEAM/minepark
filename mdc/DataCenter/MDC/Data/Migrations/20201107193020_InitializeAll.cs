using System;
using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;

namespace MDC.Data.Migrations
{
    public partial class InitializeAll : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "Bank",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    UnitId = table.Column<string>(nullable: false),
                    Cash = table.Column<double>(nullable: false),
                    Debit = table.Column<double>(nullable: false),
                    Credit = table.Column<double>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false),
                    UpdatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_Bank", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "Credentials",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    GeneratedToken = table.Column<string>(type: "nvarchar(36)", nullable: false),
                    Tag = table.Column<string>(type: "nvarchar(4096)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_Credentials", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "Users",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    FullName = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Password = table.Column<string>(type: "nvarchar(4096)", nullable: true),
                    Group = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    Licenses = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    Attributes = table.Column<string>(type: "nvarchar(128)", nullable: true),
                    People = table.Column<string>(type: "nvarchar(4096)", nullable: true),
                    Tag = table.Column<string>(type: "nvarchar(4096)", nullable: true),
                    Level = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    X = table.Column<double>(nullable: false),
                    Y = table.Column<double>(nullable: false),
                    Z = table.Column<double>(nullable: false),
                    Organisation = table.Column<int>(nullable: false),
                    Bonus = table.Column<int>(nullable: false),
                    MinutesPlayed = table.Column<int>(nullable: false),
                    Vip = table.Column<bool>(nullable: false),
                    Administrator = table.Column<bool>(nullable: false),
                    Builder = table.Column<bool>(nullable: false),
                    Realtor = table.Column<bool>(nullable: false),
                    JoinedDate = table.Column<DateTime>(nullable: false),
                    LeftDate = table.Column<DateTime>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false),
                    UpdatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_Users", x => x.Id);
                });
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "Bank");

            migrationBuilder.DropTable(
                name: "Credentials");

            migrationBuilder.DropTable(
                name: "Users");
        }
    }
}
