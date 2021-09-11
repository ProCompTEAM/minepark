using Microsoft.EntityFrameworkCore;
using MDC.Data.Models;
using MDCDatabase = MDC.Data.Database;
using MDC.Data.Models.Audit;

namespace MDC.Data
{
    public class DatabaseContext : DbContext
    {
        public DbSet<Credentials> Credentials { get; set; }

        public DbSet<User> Users { get; set; }

        public DbSet<UserSettings> UserSettings { get; set; }

        public DbSet<BankAccount> BankAccounts { get; set; }

        public DbSet<MapPoint> MapPoints { get; set; }

        public DbSet<Phone> Phones { get; set; }

        public DbSet<UnitBalance> UnitBalances { get; set; }

        public DbSet<FloatingText> FloatingTexts { get; set; }

        public DbSet<MoneyTransactionAuditRecord> MoneyTransactionAuditRecords { get; set; }

        public DbSet<ExecutedCommandAuditRecord> ExecutedCommandAuditRecords { get; set; }

        public DbSet<ChatMessageAuditRecord> ChatMessageAuditRecords { get; set; }

        public DbSet<UserTrafficAuditRecord> UserTrafficAuditRecords { get; set; }

        public DbSet<PlayerBan> PlayerBans { get; set; }

        protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
        {
            if(!MDCDatabase.IsInitialized)
            {
                MDCDatabase.Initialize();
            }

            optionsBuilder.UseLazyLoadingProxies()
                .UseMySQL(MDCDatabase.Builder.ConnectionString);
        }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            ConfigureRelationships(modelBuilder);
        }

        private void ConfigureRelationships(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<User>()
                .HasOne(user => user.Ban);
        }
    }
}
