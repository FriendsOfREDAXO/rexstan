<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// ALTER LOGFILE GROUP logfile_group ADD UNDOFILE 'file_name' [INITIAL_SIZE [=] size] [WAIT] ENGINE [=] engine_name
Assert::parseSerialize("ALTER LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' ENGINE InnoDB");
Assert::parseSerialize("ALTER LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' ENGINE = InnoDB", "ALTER LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' ENGINE InnoDB"); // [=]
Assert::parseSerialize("ALTER LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' INITIAL_SIZE 123 ENGINE InnoDB");
Assert::parseSerialize("ALTER LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' WAIT ENGINE InnoDB");


// CREATE LOGFILE GROUP logfile_group ADD UNDOFILE 'undo_file' ... ENGINE [=] engine_name
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' ENGINE InnoDB");

// [INITIAL_SIZE [=] initial_size]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' INITIAL_SIZE 123 ENGINE InnoDB");

// [UNDO_BUFFER_SIZE [=] undo_buffer_size]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' UNDO_BUFFER_SIZE 123 ENGINE InnoDB");

// [REDO_BUFFER_SIZE [=] redo_buffer_size]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' REDO_BUFFER_SIZE 123 ENGINE InnoDB");

// [NODEGROUP [=] nodegroup_id]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' NODEGROUP 123 ENGINE InnoDB");

// [WAIT]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' WAIT ENGINE InnoDB");

// [COMMENT [=] comment_text]
Assert::parseSerialize("CREATE LOGFILE GROUP grp1 ADD UNDOFILE 'file.log' COMMENT 'com1' ENGINE InnoDB");


// DROP LOGFILE GROUP logfile_group ENGINE [=] engine_name
Assert::parseSerialize("DROP LOGFILE GROUP grp1 ENGINE InnoDB");
