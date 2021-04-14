using System;

namespace MDC.Data.Dtos
{
    public class FloatingTextDto : PositionDto
    {
        public string Text { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}
