set source=%cd%
set destination=C:\wamp\www\EllieDixonMusic
mkdir %destination%
xcopy %source% %destination% /s /y
del %destination%\deploy*.bat
del %destination%\staging.php
