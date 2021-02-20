using MDC.Data.Enums;

namespace MDC.Data.Dtos
{
    public class PaymentMethodDto
    {
        public string Name { get; set; }

        public PaymentMethod Method { get; set; }
    }
}
