investigate why site shows 404 instead of document on metadata view modal's file preview, or publication view

 The logs show exactly what's happening:

file_source = D:\oldI\LiteraCommon\02-2026/magazines/amelin_gnutaya_rech_2011__izd.pdf (absolute Windows path)
disk = local (wrong!)
storage_path = content/D:\oldI\LiteraCommon\02-2026/magazines/... (nonsense path!)
file_exists = false
The bug is in two places:


FileRegistrationForm.php
 stores $fullPath (absolute Windows path) into file_source but never sets file_path
FileViewController::view() checks for Unix-like absolute paths with str_starts_with($fileSource, '/') — but on Windows they start with D:\, so that check fails
The fix needs to:

Fix FileViewController to detect Windows absolute paths too
Also fix FileRegistrationForm to properly store file_path (the relative library path) instead of file_source being the absolute path
Let me fix both:

Analyzed
Now I have a clear picture. I need to fix the 

FileViewController
 to handle Windows absolute paths (starting with a drive letter like D:\). The same fix should also apply to convertDoc and convertDocToHtml. But the most important fix is in the view() method. I also need to fix FileRegistrationForm to store the relative file_path properly.

Let me make the fixes:

Edited
+13
-15
Now I need to add the isAbsolutePath() helper method to the controller, and also fix 

FileRegistrationForm
 to properly store file_path instead of the absolute path in file_source:

Analyzed
Edited
+16
-0