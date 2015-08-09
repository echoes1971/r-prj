import sys
import os

if sys.argv[1] == '-install':
	# Desktop
	#desktop = get_special_folder_path("CSIDL_COMMON_DESKTOPDIRECTORY")
	#create_shortcut(
	#	os.path.join(sys.prefix, 'Scripts', 'rPrj.pyw'),
	#	"R-Project",
	#	os.path.join(desktop, 'rPrj.lnk'),
	#	'', '',
	#	os.path.join(sys.prefix, 'Icons', 'rprj.ico'))
	#file_created(os.path.join(desktop, 'rPrj.lnk'))
	# Start Menu
	startmenu = get_special_folder_path("CSIDL_STARTMENU")
	#startmenu = get_special_folder_path("CSIDL_COMMON_STARTMENU")
	if not os.path.exists( os.path.join(startmenu,"R-Project") ):
		os.mkdir( os.path.join(startmenu,"R-Project") )
	directory_created( os.path.join(startmenu,"R-Project") )
	create_shortcut(
		os.path.join(sys.prefix, 'Scripts', 'rPrj.pyw'),
		"R-Project",
		os.path.join(startmenu, "R-Project", 'rPrj.lnk'),
		'', '',
		os.path.join(sys.prefix, 'Icons', 'rprj.ico'))
	file_created(os.path.join(startmenu, 'rPrj.lnk')) # Registra il path con l'uninstaller
elif sys.argv[1] == '-remove':
	pass
