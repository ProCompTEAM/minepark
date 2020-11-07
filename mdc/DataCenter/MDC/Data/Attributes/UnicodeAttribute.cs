using System.ComponentModel.DataAnnotations.Schema;

namespace MDC.Data.Attributes
{
    public class UnicodeAttribute : ColumnAttribute
    {
        public UnicodeAttribute(int length)
        {
            TypeName = $"nvarchar({length})";
        }
    }
}
