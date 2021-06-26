#remove previous zip file
rm textures.zip


#update pack manifest 
$path = "textures_src\manifest.json";

$year = [int] (Get-Date -Format yyyy);
$month = [int] (Get-Date -Format MM);
$day = [int] (Get-Date -Format dd);

$manifest = Get-Content $path -raw | ConvertFrom-Json;
$manifest.header.uuid = [GUID]::NewGuid();
$manifest.header.version = ($year, $month, $day);
$manifest.modules[0].uuid = [GUID]::NewGuid();
$manifest.modules[0].version = ($year, $month, $day);
$manifest.update;
$manifest | ConvertTo-Json -depth 32| set-content $path;


#create zip file
Add-Type -Assembly "System.IO.Compression.FileSystem";
[System.IO.Compression.ZipFile]::CreateFromDirectory("textures_src", "textures.zip");