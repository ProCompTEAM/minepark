using System;
using Microsoft.EntityFrameworkCore.Migrations;
using MySql.Data.EntityFrameworkCore.Metadata;

namespace MDC.Data.Migrations
{
    public partial class AddRealtyModels : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "RealtyRegions",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    UnitId = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    World = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Category = table.Column<int>(nullable: false),
                    StartX = table.Column<double>(nullable: false),
                    StartZ = table.Column<double>(nullable: false),
                    EndX = table.Column<double>(nullable: false),
                    EndZ = table.Column<double>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_RealtyRegions", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "RealtyObjects",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    UnitId = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    Assigned = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    AreaId = table.Column<int>(nullable: false),
                    StartX = table.Column<double>(nullable: false),
                    StartY = table.Column<double>(nullable: false),
                    StartZ = table.Column<double>(nullable: false),
                    EndX = table.Column<double>(nullable: false),
                    EndY = table.Column<double>(nullable: false),
                    EndZ = table.Column<double>(nullable: false),
                    Price = table.Column<double>(nullable: false),
                    DaysBorder = table.Column<int>(nullable: false),
                    DaysAvailable = table.Column<int>(nullable: false),
                    Rental = table.Column<bool>(nullable: false),
                    AllowBuild = table.Column<bool>(nullable: false),
                    AllowMembers = table.Column<bool>(nullable: false),
                    RentedDate = table.Column<DateTime>(nullable: false),
                    CreatedDate = table.Column<DateTime>(nullable: false),
                    UpdatedDate = table.Column<DateTime>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_RealtyObjects", x => x.Id);
                    table.ForeignKey(
                        name: "FK_RealtyObjects_RealtyRegions_AreaId",
                        column: x => x.AreaId,
                        principalTable: "RealtyRegions",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "RealtyMembers",
                columns: table => new
                {
                    Id = table.Column<int>(nullable: false)
                        .Annotation("MySQL:ValueGenerationStrategy", MySQLValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    UnitId = table.Column<string>(type: "nvarchar(128)", nullable: false),
                    ParentId = table.Column<int>(nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_RealtyMembers", x => x.Id);
                    table.ForeignKey(
                        name: "FK_RealtyMembers_RealtyObjects_ParentId",
                        column: x => x.ParentId,
                        principalTable: "RealtyObjects",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateIndex(
                name: "IX_RealtyMembers_ParentId",
                table: "RealtyMembers",
                column: "ParentId");

            migrationBuilder.CreateIndex(
                name: "IX_RealtyObjects_AreaId",
                table: "RealtyObjects",
                column: "AreaId");
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            throw new NotImplementedException();
        }
    }
}
