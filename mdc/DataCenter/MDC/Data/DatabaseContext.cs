using Microsoft.EntityFrameworkCore;
using MDC.Data.Models;
using MDCDatabase = MDC.Data.Database;

namespace MDC.Data
{
    public class DatabaseContext : DbContext
    {
        public DbSet<Credentials> Credentials { get; set; }

        public DbSet<User> Users { get; set; }

        public DbSet<BankAccount> BankAccounts { get; set; }

        public DbSet<MapPoint> MapPoints { get; set; }

        public DbSet<Phone> Phones { get; set; }

        public DbSet<UnitBalance> UnitBalances { get; set; }

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
        }
    }
}
