"""
http://docs.python.org/distutils/setupscript.html
http://docs.python.org/distutils/builtdist.html

c:\Python27\python.exe setup.py bdist_wininst --install-script=rPrj_postinstall.py

# Update PyPi
python setup.py register
python setup.py sdist upload
# Update installations
sudo easy_install --upgrade r-prj
sudo pip install --upgrade r-prj
"""

import sys
from distutils.core import setup

my_scripts = ['rPrj.pyw', ]
my_data_files = []
if sys.platform == 'win32':
    my_data_files.append(('Icons', ['rprj/formulator/icons/rprj.ico']))
    my_scripts.append('rPrj_postinstall.py')
elif sys.platform == 'darwin':
    # my_data_files.append( ('Resources/RPrj.app/Contents',['RPrj.app/Contents/Info.plist']) )
    # my_data_files.append( ('Resources/RPrj.app/Contents/MacOS',['RPrj.app/Contents/MacOS/osx.sh']) )
    # my_data_files.append( ('Resources/RPrj.app/Contents/Resources',['RPrj.app/Contents/Resources/logo_32x32.icns']) )
    pass
else:
    my_data_files.append(('/usr/share/icons', ['rprj/formulator/icons/rprj.png']))
    my_data_files.append(('/usr/share/applications', ['rprj.desktop']))

setup(
    name='R-Prj',
    version="0.5.6",
    description='R-Prj framework libraries and client application',
    license="http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)",
    author='Roberto Rocco Angeloni',
    author_email='roberto@roccoangeloni.it',
    url='http://www.kware.it/main.php?obj_id=uuid69742e34663137643339336236653937',
    # url='https://sourceforge.net/projects/r-prj/',
    # download_url='https://sourceforge.net/projects/r-prj/',
    requires=['pyqt', ],
    packages=['rprj',
              'rprj/apps',
              'rprj/dblayer',
              'rprj/formulator',
              'rprj/net',
              'rprj/qtwidgets',
              ],
    package_data={
        'rprj/qtwidgets': ['rprj/qtwidgets/*.png'], 'rprj/apps/images': ['rprj/apps/images/linneighborhood.png']},
    data_files=my_data_files,
    # ('/usr/share/icons',['rprj/apps/icons/rprj.png'])
    # ,('/usr/share/applications',['rprj.desktop'])
    #     ('bitmaps',['rprj/qtwidgets/format-fill-color.png'
    #         ,'rprj/qtwidgets/format-indent-less.png'
    #         ,'rprj/qtwidgets/format-indent-more.png'
    #         ,'rprj/qtwidgets/'
    #         ]
    #     ),
    # ]
    scripts=my_scripts,
    )
