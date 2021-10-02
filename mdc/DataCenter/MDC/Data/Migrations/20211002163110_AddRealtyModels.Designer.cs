﻿// <auto-generated />
using System;
using MDC.Data;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;
using Microsoft.EntityFrameworkCore.Storage.ValueConversion;

namespace MDC.Data.Migrations
{
    [DbContext(typeof(DatabaseContext))]
    [Migration("20211002163110_AddRealtyModels")]
    partial class AddRealtyModels
    {
        protected override void BuildTargetModel(ModelBuilder modelBuilder)
        {
#pragma warning disable 612, 618
            modelBuilder
                .HasAnnotation("ProductVersion", "3.1.9")
                .HasAnnotation("Relational:MaxIdentifierLength", 64);

            modelBuilder.Entity("MDC.Data.Models.Audit.ChatMessageAuditRecord", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Message")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("Subject")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.ToTable("ChatMessageAuditRecords");
                });

            modelBuilder.Entity("MDC.Data.Models.Audit.ExecutedCommandAuditRecord", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<string>("Command")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Subject")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.ToTable("ExecutedCommandAuditRecords");
                });

            modelBuilder.Entity("MDC.Data.Models.Audit.MoneyTransactionAuditRecord", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<double>("Amount")
                        .HasColumnType("double");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Subject")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("TargetAccount")
                        .HasColumnType("int");

                    b.Property<int>("TransactionType")
                        .HasColumnType("int");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.ToTable("MoneyTransactionAuditRecords");
                });

            modelBuilder.Entity("MDC.Data.Models.Audit.UserTrafficAuditRecord", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Subject")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("UserTrafficType")
                        .HasColumnType("int");

                    b.HasKey("Id");

                    b.ToTable("UserTrafficAuditRecords");
                });

            modelBuilder.Entity("MDC.Data.Models.BankAccount", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<double>("Cash")
                        .HasColumnType("double");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<double>("Credit")
                        .HasColumnType("double");

                    b.Property<double>("Debit")
                        .HasColumnType("double");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("PaymentMethod")
                        .HasColumnType("int");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<DateTime>("UpdatedDate")
                        .HasColumnType("datetime");

                    b.HasKey("Id");

                    b.ToTable("BankAccounts");
                });

            modelBuilder.Entity("MDC.Data.Models.Credentials", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<string>("GeneratedToken")
                        .IsRequired()
                        .HasColumnType("nvarchar(36)");

                    b.Property<string>("Tag")
                        .HasColumnType("nvarchar(4096)");

                    b.HasKey("Id");

                    b.ToTable("Credentials");
                });

            modelBuilder.Entity("MDC.Data.Models.FloatingText", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Text")
                        .IsRequired()
                        .HasColumnType("nvarchar(4096)");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("World")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<double>("X")
                        .HasColumnType("double");

                    b.Property<double>("Y")
                        .HasColumnType("double");

                    b.Property<double>("Z")
                        .HasColumnType("double");

                    b.HasKey("Id");

                    b.ToTable("FloatingTexts");
                });

            modelBuilder.Entity("MDC.Data.Models.MapPoint", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<int>("GroupId")
                        .HasColumnType("int");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("World")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<double>("X")
                        .HasColumnType("double");

                    b.Property<double>("Y")
                        .HasColumnType("double");

                    b.Property<double>("Z")
                        .HasColumnType("double");

                    b.HasKey("Id");

                    b.ToTable("MapPoints");
                });

            modelBuilder.Entity("MDC.Data.Models.Phone", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<double>("Balance")
                        .HasColumnType("double");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<long>("Number")
                        .HasColumnType("bigint");

                    b.Property<string>("Subject")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("SubjectType")
                        .HasColumnType("int");

                    b.Property<DateTime>("UpdatedDate")
                        .HasColumnType("datetime");

                    b.HasKey("Id");

                    b.ToTable("Phones");
                });

            modelBuilder.Entity("MDC.Data.Models.Realty.RealtyArea", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<int>("Category")
                        .HasColumnType("int");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<double>("EndX")
                        .HasColumnType("double");

                    b.Property<double>("EndZ")
                        .HasColumnType("double");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<double>("StartX")
                        .HasColumnType("double");

                    b.Property<double>("StartZ")
                        .HasColumnType("double");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("World")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.ToTable("RealtyRegions");
                });

            modelBuilder.Entity("MDC.Data.Models.Realty.RealtyMember", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("ParentId")
                        .HasColumnType("int");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.HasIndex("ParentId");

                    b.ToTable("RealtyMembers");
                });

            modelBuilder.Entity("MDC.Data.Models.Realty.RealtyObject", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<bool>("AllowBuild")
                        .HasColumnType("tinyint(1)");

                    b.Property<bool>("AllowMembers")
                        .HasColumnType("tinyint(1)");

                    b.Property<int>("AreaId")
                        .HasColumnType("int");

                    b.Property<string>("Assigned")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<int>("DaysAvailable")
                        .HasColumnType("int");

                    b.Property<int>("DaysBorder")
                        .HasColumnType("int");

                    b.Property<double>("EndX")
                        .HasColumnType("double");

                    b.Property<double>("EndY")
                        .HasColumnType("double");

                    b.Property<double>("EndZ")
                        .HasColumnType("double");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<double>("Price")
                        .HasColumnType("double");

                    b.Property<bool>("Rental")
                        .HasColumnType("tinyint(1)");

                    b.Property<DateTime>("RentedDate")
                        .HasColumnType("datetime");

                    b.Property<double>("StartX")
                        .HasColumnType("double");

                    b.Property<double>("StartY")
                        .HasColumnType("double");

                    b.Property<double>("StartZ")
                        .HasColumnType("double");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<DateTime>("UpdatedDate")
                        .HasColumnType("datetime");

                    b.HasKey("Id");

                    b.HasIndex("AreaId");

                    b.ToTable("RealtyObjects");
                });

            modelBuilder.Entity("MDC.Data.Models.UnitBalance", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<double>("Balance")
                        .HasColumnType("double");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.HasKey("Id");

                    b.ToTable("UnitBalances");
                });

            modelBuilder.Entity("MDC.Data.Models.User", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<bool>("Administrator")
                        .HasColumnType("tinyint(1)");

                    b.Property<int>("Bonus")
                        .HasColumnType("int");

                    b.Property<bool>("Builder")
                        .HasColumnType("tinyint(1)");

                    b.Property<DateTime>("CreatedDate")
                        .HasColumnType("datetime");

                    b.Property<string>("Email")
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("FullName")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("Group")
                        .HasColumnType("nvarchar(128)");

                    b.Property<DateTime>("JoinedDate")
                        .HasColumnType("datetime");

                    b.Property<DateTime>("LeftDate")
                        .HasColumnType("datetime");

                    b.Property<int>("MinutesPlayed")
                        .HasColumnType("int");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("Password")
                        .HasColumnType("nvarchar(4096)");

                    b.Property<string>("People")
                        .HasColumnType("nvarchar(4096)");

                    b.Property<bool>("Realtor")
                        .HasColumnType("tinyint(1)");

                    b.Property<string>("Tag")
                        .HasColumnType("nvarchar(4096)");

                    b.Property<DateTime>("UpdatedDate")
                        .HasColumnType("datetime");

                    b.Property<bool>("Vip")
                        .HasColumnType("tinyint(1)");

                    b.HasKey("Id");

                    b.ToTable("Users");
                });

            modelBuilder.Entity("MDC.Data.Models.UserSettings", b =>
                {
                    b.Property<int>("Id")
                        .ValueGeneratedOnAdd()
                        .HasColumnType("int");

                    b.Property<string>("Attributes")
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("Licenses")
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("Name")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<int>("Organisation")
                        .HasColumnType("int");

                    b.Property<string>("UnitId")
                        .IsRequired()
                        .HasColumnType("nvarchar(128)");

                    b.Property<string>("World")
                        .HasColumnType("nvarchar(128)");

                    b.Property<double>("X")
                        .HasColumnType("double");

                    b.Property<double>("Y")
                        .HasColumnType("double");

                    b.Property<double>("Z")
                        .HasColumnType("double");

                    b.HasKey("Id");

                    b.ToTable("UserSettings");
                });

            modelBuilder.Entity("MDC.Data.Models.Realty.RealtyMember", b =>
                {
                    b.HasOne("MDC.Data.Models.Realty.RealtyObject", "Parent")
                        .WithMany("Members")
                        .HasForeignKey("ParentId")
                        .OnDelete(DeleteBehavior.Cascade)
                        .IsRequired();
                });

            modelBuilder.Entity("MDC.Data.Models.Realty.RealtyObject", b =>
                {
                    b.HasOne("MDC.Data.Models.Realty.RealtyArea", "Area")
                        .WithMany("Objects")
                        .HasForeignKey("AreaId")
                        .OnDelete(DeleteBehavior.Cascade)
                        .IsRequired();
                });
#pragma warning restore 612, 618
        }
    }
}
