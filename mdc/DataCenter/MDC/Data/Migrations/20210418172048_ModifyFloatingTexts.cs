using Microsoft.EntityFrameworkCore.Migrations;

namespace MDC.Data.Migrations
{
    public partial class ModifyFloatingTexts : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AlterColumn<int>(
                name: "Z",
                table: "FloatingTexts",
                nullable: false,
                oldClrType: typeof(double),
                oldType: "double");

            migrationBuilder.AlterColumn<int>(
                name: "Y",
                table: "FloatingTexts",
                nullable: false,
                oldClrType: typeof(double),
                oldType: "double");

            migrationBuilder.AlterColumn<int>(
                name: "X",
                table: "FloatingTexts",
                nullable: false,
                oldClrType: typeof(double),
                oldType: "double");
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AlterColumn<double>(
                name: "Z",
                table: "FloatingTexts",
                type: "double",
                nullable: false,
                oldClrType: typeof(int));

            migrationBuilder.AlterColumn<double>(
                name: "Y",
                table: "FloatingTexts",
                type: "double",
                nullable: false,
                oldClrType: typeof(int));

            migrationBuilder.AlterColumn<double>(
                name: "X",
                table: "FloatingTexts",
                type: "double",
                nullable: false,
                oldClrType: typeof(int));
        }
    }
}
