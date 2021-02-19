using AutoMapper;
using MDC.Data.Dtos;
using MDC.Data.Models;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;
using MDC.Infrastructure.Services.Interfaces;
using MDC.Utilities;
using System;

namespace MDC.Infrastructure.Services
{
    public class UsersService : IUsersService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IDateTimeProvider dateTimeProvider;

        private readonly IPhonesService phonesService;

        private readonly IBankingService bankingService;

        private readonly IMapper mapper;

        public UsersService()
        {
            databaseProvider = Store.GetProvider<DatabaseProvider>();
            dateTimeProvider = Store.GetProvider<DateTimeProvider>();
            phonesService = Store.GetService<PhonesService>();
            bankingService = Store.GetService<BankingService>();
            mapper = Store.GetMapper();
        }

        public bool Exist(string userName)
        {
            return databaseProvider.Any<User>(u => u.Name == userName);
        }

        public User GetUser(string userName)
        {
            return databaseProvider.SingleOrDefault<User>(u => u.Name.ToLower() == userName.ToLower());
        }

        public User GetUser(int userId)
        {
            return databaseProvider.GetById<User>(userId);
        }

        public UserDto GetUserDto(string userName)
        {
            User user = GetUser(userName);
            UserDto userDto = mapper.Map<UserDto>(user);
            userDto.PhoneNumber = (long) phonesService.GetNumberForUser(userName);
            return userDto;
        }

        public string GetPassword(string userName)
        {
            return GetUser(userName).Password;
        }

        public bool ExistPassword(string userName)
        {
            return GetUser(userName).Password != null;
        }

        public void SetPassword(string userName, string password)
        {
            User user = GetUser(userName);
            user.Password = password;
            databaseProvider.Update(user);
            databaseProvider.Commit();
        }

        public void ResetPassword(string userName)
        {
            SetPassword(userName, null);
        }

        public void Create(UserDto userDto)
        {
            ValidateIsUserExist(userDto.Name);

            User user = mapper.Map<User>(userDto);
            databaseProvider.Create(user);
            databaseProvider.Commit();

            phonesService.CreateNumberForUser(user.Name);
            bankingService.CreateEmptyBankAccount(user.Name);
        }

        public UserDto CreateInternal(string userName)
        {
            ValidateIsUserExist(userName);

            User user = GetDefaultUserTemplate(userName);
            databaseProvider.Create(user);
            databaseProvider.Commit();

            bankingService.CreateEmptyBankAccount(userName);

            long phoneNumber = phonesService.CreateNumberForUser(user.Name);

            UserDto userDto = mapper.Map<UserDto>(user);
            userDto.PhoneNumber = phoneNumber;

            return userDto;
        }

        public void Update(UserDto userDto)
        {
            User user = GetUser(userDto.Name);

            user = ObjectComparer.Merge(user, userDto, 
                    u => u.Id,
                    u => u.Name,
                    u => u.MinutesPlayed,
                    u => u.JoinedDate,
                    u => u.LeftDate,
                    u => u.CreatedDate,
                    u => u.UpdatedDate
                );
            
            databaseProvider.Update(user);
            databaseProvider.Commit();
        }

        public void UpdateJoinStatus(string userName)
        {
            User user = GetUser(userName);
            user.JoinedDate = dateTimeProvider.Now;
            databaseProvider.Update(user);
            databaseProvider.Commit();
        }

        public void UpdateQuitStatus(string userName)
        {
            User user = GetUser(userName);
            user.LeftDate = dateTimeProvider.Now;
            user.MinutesPlayed += GetMinutesLeft(user.JoinedDate, user.LeftDate);
            databaseProvider.Update(user);
            databaseProvider.Commit();
        }

        private int GetMinutesLeft(DateTime joinedDate, DateTime leftDate)
        {
            return (int) (leftDate - joinedDate).TotalMinutes;
        }

        private string CreateFullName(string userName)
        {
            return userName.Replace('_', ' ');
        }

        private User GetDefaultUserTemplate(string userName)
        {
            return new User
            {
                Name = userName,
                FullName = CreateFullName(userName),
                Level = string.Empty,
                X = 0,
                Y = 0,
                Z = 0,
                Organisation = 0,
                Bonus = 3,
                MinutesPlayed = 0,
                Vip = false,
                Administrator = false,
                Builder = false,
                Realtor = false
            };
        }

        private void ValidateIsUserExist(string userName)
        {
            if (Exist(userName))
            {
                throw new InvalidOperationException("User already exists.");
            }
        }
    }
}
