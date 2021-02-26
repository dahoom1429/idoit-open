<?php

/**
 * i-doit
 *
 * Database exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_database_mysql extends isys_exception
{
    /**
     * Error code mapping
     *
     * @var array
     */
    protected $errorMap = [
        1   => 'OS error code: Operation not permitted',
        2   => 'OS error code: No such file or directory',
        3   => 'OS error code: No such process',
        4   => 'OS error code: Interrupted system call',
        5   => 'OS error code: Input/output error',
        6   => 'OS error code: No such device or address',
        7   => 'OS error code: Argument list too long',
        8   => 'OS error code: Exec format error',
        9   => 'OS error code: Bad file descriptor',
        10  => 'OS error code: No child processes',
        11  => 'OS error code: Resource temporarily unavailable',
        12  => 'OS error code: Cannot allocate memory',
        13  => 'OS error code: Permission denied',
        14  => 'OS error code: Bad address',
        15  => 'OS error code: Block device required',
        16  => 'OS error code: Device or resource busy',
        17  => 'OS error code: File exists',
        18  => 'OS error code: Invalid cross-device link',
        19  => 'OS error code: No such device',
        20  => 'OS error code: Not a directory',
        21  => 'OS error code: Is a directory',
        22  => 'OS error code: Invalid argument',
        23  => 'OS error code: Too many open files in system',
        24  => 'OS error code: Too many open files',
        25  => 'OS error code: Inappropriate ioctl for device',
        26  => 'OS error code: Text file busy',
        27  => 'OS error code: File too large',
        28  => 'OS error code: No space left on device',
        30  => 'OS error code: Read-only file system',
        31  => 'OS error code: Too many links',
        32  => 'OS error code: Broken pipe',
        33  => 'OS error code: Numerical argument out of domain',
        34  => 'OS error code: Numerical result out of range',
        35  => 'OS error code: Resource deadlock avoided',
        36  => 'OS error code: File name too long',
        37  => 'OS error code: No locks available',
        38  => 'OS error code: Function not implemented',
        39  => 'OS error code: Directory not empty',
        40  => 'OS error code: Too many levels of symbolic links',
        42  => 'OS error code: No message of desired type',
        43  => 'OS error code: Identifier removed',
        44  => 'OS error code: Channel number out of range',
        45  => 'OS error code: Level 2 not synchronized',
        46  => 'OS error code: Level 3 halted',
        47  => 'OS error code: Level 3 reset',
        48  => 'OS error code: Link number out of range',
        49  => 'OS error code: Protocol driver not attached',
        50  => 'OS error code: No CSI structure available',
        51  => 'OS error code: Level 2 halted',
        52  => 'OS error code: Invalid exchange',
        53  => 'OS error code: Invalid request descriptor',
        54  => 'OS error code: Exchange full',
        55  => 'OS error code: No anode',
        56  => 'OS error code: Invalid request code',
        57  => 'OS error code: Invalid slot',
        59  => 'OS error code: Bad font file format',
        60  => 'OS error code: Device not a stream',
        61  => 'OS error code: No data available',
        62  => 'OS error code: Timer expired',
        63  => 'OS error code: Out of streams resources',
        64  => 'OS error code: Machine is not on the network',
        65  => 'OS error code: Package not installed',
        66  => 'OS error code: Object is remote',
        67  => 'OS error code: Link has been severed',
        68  => 'OS error code: Advertise error',
        69  => 'OS error code: Srmount error',
        70  => 'OS error code: Communication error on send',
        71  => 'OS error code: Protocol error',
        72  => 'OS error code: Multihop attempted',
        73  => 'OS error code: RFS specific error',
        74  => 'OS error code: Bad message',
        75  => 'OS error code: Value too large for defined data type',
        76  => 'OS error code: Name not unique on network',
        77  => 'OS error code: File descriptor in bad state',
        78  => 'OS error code: Remote address changed',
        79  => 'OS error code: Can not access a needed shared library',
        80  => 'OS error code: Accessing a corrupted shared library',
        81  => 'OS error code: .lib section in a.out corrupted',
        82  => 'OS error code: Attempting to link in too many shared libraries',
        83  => 'OS error code: Cannot exec a shared library directly',
        84  => 'OS error code: Invalid or incomplete multibyte or wide character',
        85  => 'OS error code: Interrupted system call should be restarted',
        86  => 'OS error code: Streams pipe error',
        87  => 'OS error code: Too many users',
        88  => 'OS error code: Socket operation on non-socket',
        89  => 'OS error code: Destination address required',
        90  => 'OS error code: Message too long',
        91  => 'OS error code: Protocol wrong type for socket',
        92  => 'OS error code: Protocol not available',
        93  => 'OS error code: Protocol not supported',
        94  => 'OS error code: Socket type not supported',
        95  => 'OS error code: Operation not supported',
        96  => 'OS error code: Protocol family not supported',
        97  => 'OS error code: Address family not supported by protocol',
        98  => 'OS error code: Address already in use',
        99  => 'OS error code: Cannot assign requested address',
        100 => 'OS error code: Network is down',
        101 => 'OS error code: Network is unreachable',
        102 => 'OS error code: Network dropped connection on reset',
        103 => 'OS error code: Software caused connection abort',
        104 => 'OS error code: Connection reset by peer',
        105 => 'OS error code: No buffer space available',
        106 => 'OS error code: Transport endpoint is already connected',
        107 => 'OS error code: Transport endpoint is not connected',
        108 => 'OS error code: Cannot send after transport endpoint shutdown',
        109 => 'OS error code: Too many references: cannot splice',
        110 => 'OS error code: Connection timed out',
        111 => 'OS error code: Connection refused',
        112 => 'OS error code: Host is down',
        113 => 'OS error code: No route to host',
        114 => 'OS error code: Operation already in progress',
        115 => 'OS error code: Operation now in progress',
        116 => 'OS error code: Stale NFS file handle',
        117 => 'OS error code: Structure needs cleaning',
        118 => 'OS error code: Not a XENIX named type file',
        119 => 'OS error code: No XENIX semaphores available',
        120 => 'OS error code: Is a named type file',
        121 => 'OS error code: Remote I/O error',
        122 => 'OS error code: Disk quota exceeded',
        123 => 'OS error code: No medium found',
        124 => 'OS error code: Wrong medium type',
        125 => 'OS error code: Operation canceled',
        126 => 'MySQL error code: Index file is crashed',
        127 => 'MySQL error code: Record-file is crashed',
        128 => 'MySQL error code: Out of memory',
        130 => 'MySQL error code: Incorrect file format',
        131 => 'MySQL error code: Command not supported by database',
        132 => 'MySQL error code: Old database file',
        133 => 'MySQL error code: No record read before update',
        134 => 'MySQL error code: Record was already deleted (or record file crashed)',
        135 => 'MySQL error code: No more room in record file',
        136 => 'MySQL error code: No more room in index file',
        137 => 'MySQL error code: No more records (read after end of file)',
        138 => 'MySQL error code: Unsupported extension used for table',
        139 => 'MySQL error code: Too big row',
        140 => 'MySQL error code: Wrong create options',
        141 => 'MySQL error code: Duplicate unique key or constraint on write or update',
        142 => 'MySQL error code: Unknown character set used',
        143 => 'MySQL error code: Conflicting table definitions in sub-tables of MERGE table',
        144 => 'MySQL error code: Table is crashed and last repair failed',
        145 => 'MySQL error code: Table was marked as crashed and should be repaired',
        146 => 'MySQL error code: Lock timed out; Retry transaction',
        147 => 'MySQL error code: Lock table is full;  Restart program with a larger locktable',
        148 => 'MySQL error code: Updates are not allowed under a read only transactions',
        149 => 'MySQL error code: Lock deadlock; Retry transaction',
        150 => 'MySQL error code: Foreign key constraint is incorrectly formed',
        151 => 'MySQL error code: Cannot add a child row',
        152 => 'MySQL error code: Cannot delete a parent row'
    ];

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  array   $p_dbinfo
     * @param  integer $p_errorcode
     * @param  boolean $p_write_log
     */
    public function __construct($p_message, $p_dbinfo = [], $p_errorcode = 0, $p_write_log = true)
    {
        if (isset($this->errorMap[$p_errorcode])) {
            $p_message .= ' | ' . $this->errorMap[$p_errorcode];
        }

        parent::__construct("Database error : $p_message\n", var_export($p_dbinfo, true), $p_errorcode, 'exception', $p_write_log);
    }
}