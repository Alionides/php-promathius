<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Contrib Installer (c) 2005 Rinon
  Released under the GNU General Public License
*/

//2.2 b
define('INSTALL_CONTRIB_INSTALLER','����������');
define('INTRO','<br>�������� ����� � ������� ������. ����������� ��� ���� ���������.
<br><b>����� CIP.NET.UA:</b><br>
�� ����� ��������� ������������ �������� ��������� ����� ���� ������ � ���� ������.
<br>
���� �� ������ ������������ ��� �������, �� ��� ����� ������� ����������� � ��� ����� � ���� ������ ����� ��������������.<br>
�� �������� ������� ��� ���� ���������, ������� ���� ������� ����� ��������� ������������.');


//2.2 e


define('CONTRIB_INSTALLER_NAME','��������� �������');
define('CONFIG_FILENAME','install.xml');
define('INIT_CONTRIB_INSTALLER', 'contrib_installer.php');

define('INIT_CONTRIB_INSTALLER_TEXT', '��������� �������');
define('CONTRIB_INSTALLER_TEXT', '��������� �������');

//=========================
define('ALL_CHANGES_WILL_BE_REMOVED_TEXT', '��� ��������� ��������� ���� �������.');
//=========================
define('AUTHOR_TEXT', '�����: ');
define('FROM_INSTALL_FILE_TEXT', '������������ ����: ');
//=========================
define('INSTALLING_CONTRIBUTION_TEXT', '������������� ������: ');
define('REMOVING_CONTRIBUTION_TEXT', '������� ������: ');
//=========================
define('CANT_CREATE_DIR_TEXT', '�� ���� ������� ����������: ');
define('CANT_WRITE_TO_DIR_TEXT', '�� ���� �������� � ����: ');
define('COLUDNT_REMOVE_DIR_TEXT', '�� ���� ������� ����������: ');
//=========================
define('REMOVING_DIRS_IN_BOLD', '������� ����������: ');
define('CREATING_DIRS_IN_BOLD', '������ ����������: ');
//=========================
define('WRITE_PERMISSINS_NEEDED_TEXT', '���������� ����� ������� �� ������ ���: ');
define('ADD_CODE_IN_FILE_TEXT', '����� ��� � �����: ');
define('EXPRESSION_TEXT', '���: ');
define('AFTER_EXPRESSION_ADD_TEXT', '������������ ���: ');
define('ORIGINAL_AFTER_EXPRESSION_ADD_TEXT', '����� ��� ����� ���������: ');
define('UNDO_ADD_CODE_IN_FILE_TEXT', '�������� ���������� ���� � ����: ');
define('ORIGINAL_EXPRESSION_TEXT', '������������ ���: ');
define('ORIGINAL_REPLACE_WITH_TEXT', '������ ��: ');
//=========================
define('CONFLICT_IN_FILE_TEXT', '�������� � �����: ');
define('CANT_READ_FILE', '���� �����������: ');
define('REMOVING_FILE_TEXT', '������� ����: ');
define('COULDNT_REMOVE_FILE_TEXT', '�� ���� ������� ����: ');
define('COULDNT_COPY_TO_TEXT', '�� ���� ����������� ����: ');

//=========================
define('COULDNT_FIND_TEXT', '�� ���� ����� ');
//define('CANT_OPEN_FOR_WRITING_TEXT', '�� ���� ������� ���� ��� ������: ');
//=========================
define('CONTRIBUTION_DIR_TEXT', '���������� � ��������: ');
define('NO_CONTRIBUTION_NAME_TEXT', '�� ������� �������� ������.');
//=========================
define('NO_FILE_TAG_IN_ADDFILE_SECTION_TEXT', '��� ���� file.');
define('NAME_OF_FILE_MISSING_IN_ADDFILE_SECTION_TEXT', '�������� �������������� �����.');

define('NO_QUERY_TAG_IN_SQL_SECTION_TEXT', '��� ���� query.');
define('NO_REMOVE_QUERY_NESSESARY_FOR_SQL_QUERY_TEXT', '��� ������������ ������� �� �������� ��� SQL �������: ');
define('RUN_SQL_REMOVE_QUERY_TEXT', '��������� SQL ������ �� ��������: ');
define('RUN_SQL_QUERY_TEXT', '��������� SQL ������: ');

//=========================
define('NO_DIR_TAG_IN_MAKE_DIR_SECTION_TEXT', '��� ���� dir.');
define('NAME_OF_DIR_MISSING_IN_MAKE_DIR_SECTION_TEXT', '�������� ������������� ����������.');
define('NAME_OF_PARENT_DIR_MISSING_IN_MAKE_DIR_SECTION_TEXT', '�������� ��� parent_dir �����������.');

define('ERROR_IN_ADDCODE_SECTION_TEXT', '������ � <addcode>');
define('COPYING_TO_TEXT', '�������� �: ');
define('FIND_REPLACE_IN_FILE_TEXT', '����� � ������ � �����: ');
define('ERROR_IN_FINDREPLACE_SECTION_TEXT', '������ � <findreplace>');
define('UNDO_FIND_REPLACE_IN_FILE_TEXT', '�������� ����� � ������ � �����: ');

define('REPLACE_WITH_TEXT', '��������: ');
define('ON_LINE_TEXT', '� ������ ');
//=========================
define('UPDATE_BUTTON_TEXT', '��������');
define('IN_THE_FILE_TEXT', '� �����: ');

define('INSTALL_XML_FILE_IS_VALID_TEXT', '���� install.xml ��� ������.');
define('PERMISSIONS_IS_VALID_TEXT', '����� ������� ����������.');

define('INSTALLATION_COMPLETE_TEXT', '����������.');
define('REMOVING_COMPLETE_TEXT', '�����.');


// Subheaders
define('COMMENTS_TEXT', '�����������: ');
define('CHECKING_CONFIG_FILE_TEXT', '��������� ���� ��������: ');
define('CHECKING_PERMISSIONS_TEXT', '��������� ����� �������: ');
define('CHECKING_CONFLICTS_TEXT', '��������� ���������:');

//define('RUNNING_TEXT', '���������: ');
define('RUNNING_TEXT', '��� ��������� �������: ');//1.0.4

define('STATUS_TEXT', '������: ');

define('NO_CONFLICTS_TEXT', '��� ����������.');
define('PHP_INSTALL_TEXT', '��������������� PHP ���: ');
define('PHP_REMOVE_TEXT', '��������� PHP ���: ');

define('PHP_RUNTIME_MESSAGES_TEXT', '��������� PHP: ');

define('NO_INSTALL_TAG_IN_PHP_SECTION_TEXT', '��� ���� INSTALL.');
define('NO_REMOVE_TAG_IN_PHP_SECTION_TEXT', '��� ���� REMOVE.');


define('FILE_EXISTS_TEXT', '���� ����������');
define('FILE_NOT_EXISTS_TEXT', '���� �� ������');

define('LINK_EXISTS_TEXT', '������ ����������.');



define('NAME_OF_FILE_MISSING_IN_DEL_FILE_SECTION_TEXT', '�������� �������������� �����.');
define('MD5_SUM_UPDATED_TEXT', 'MD5 ����� ���������.');
define('MD5_SUM_REMOVED_TEXT', 'MD5 ����� �������.');

define('FILE_EXISTS_AND_WAS_CHANGED_TEXT', '���� ��� ��� ������ ������ �������. �� ������: <br>
- ������� ��������� ����� �����,<br>
- ������� ������������ ����, ��� ���������,<br>
- ���������� ������,<br>
- ����� ��� ��������� � ����� � ��������� � ���������� (�������� �������������),<br>
- ��������� ��������� �� ������������� ����� � ����, ��������� ������������,<br>
- �����������. <br>');
define('ERROR_COULD_NOT_OPEN_XML', '�� ���� ������� XML �: ');
define('ERROR_XML', '������ XML: ');
define('TEXT_AT_LINE', ' � ������ ');

//1.0.6:
define('TEXT_NOT_ORIGINAL_TEXT', '�� ������������ ����� find �������. ');
define('TEXT_HAVE_BEEN_FOUND', '��� ������ ');
define('TEXT_TIMES', ' ���!');

define('TEXT_HOW_TO_RESOVLE_CONFLICTS', '
This error message means that CIP (Contrib Installer Package) that you are installing tryes to change lines in file that was changed by another CIP before.<br>
If file was changed by hand you will see a message that says "Can\'t find...".<br>
<br>
<b>What to do?</b><br>
<br>
1.Open file from osCommerce and find lines that installed CIP tryes to change. <br>
You can see a comments above and below this lines.<br>
In comments you can find information about CIP that added/changes this lines.<br>
2. If CIP that make changes before don\'t really needed - remove them and install your CIP.<br>
If needed - read #3.<br>
<br>
3. Make a copy of CIP that you want to install.<br>
4. Change install.xml from copy of CIP.<br>
Use file from osCommerce that must be changed for that.<br>
<br>
May be you will find useful to compare 2 files <br>
use <i>diff /.../one_file.php /.../other_file.php -bu > 1.txt</i><br>
where<br>
other_file.php - file from osCommerce that have a conflict<br>
one_file.php - file from clear osCommerce or just remove CIP that changed lines that your CIP tryes change too.<br>
5. Try to install your new CIP.<br>
6. If all works. Add your name in install.xml an section "credits" and upload on oscommerce site :-)<br>
<br>
or<br>
<br>
ask a help on Contrib Installer forum.<br>');


//1.0.10


define('NO_COMMENTS_TAG_IN_DESCRIPTION_SECTION_TEXT', '��� ���� comments � ������� ��������');
define('NO_CREDITS_TAG_IN_DESCRIPTION_SECTION_TEXT', '��� ���� credits � ������� ��������');

define('NO_DETAILS_TAG_IN_DESCRIPTION_SECTION_TEXT', '��� ���� details � ������� ��������');

define('NO_CONTRIB_REF_PARAMETER_IN_DETAILS_TAG_TEXT', '��� ��������� contrib_ref � ���� details');
define('NO_FORUM_REF_PARAMETER_IN_DETAILS_TAG_TEXT', '��� ��������� forum_ref � ���� details');
define('NO_CONTRIB_TYPE_PARAMETER_IN_DETAILS_TAG_TEXT', '��� ��������� contrib_type � ���� details');
define('NO_STATUS_PARAMETER_IN_DETAILS_TAG_TEXT', '��� ��������� status � ���� details');
define('NO_LAST_UPDATE_PARAMETER_IN_DETAILS_TAG_TEXT', '��� ��������� last_update � ���� details');


//1.0.13
define('CHOOSE_A_CONTRIBUTION_TEXT', '
<a href="http://www.oscommerce.com/community?contributions=&search=Contrib+Installer&category=all" target=_blank">������ ������ �� ����� osCommerce</a> ��� �������� ������: ');


//1.0.14
define('IMAGE_BUTTON_INSTALL', '����������');
define('IMAGE_BUTTON_REMOVE', '�������');

/*
define('

', '

');
define('

', '

');

*/
?>