public static class StringUtility
{
    public static string CutWithEnding(string source, int newLength, string ending = "...")
    {
        return source.Substring(0, newLength - ending.Length) + ending;
    }
}