function raidcalc(p_disks, p_space, p_raidtype, p_target) {
    var l_num_disks      = parseInt(p_disks),
        l_space          = '-',
        l_message        = '',
        l_diskspace_each = parseFloat(p_space),
        l_diskspace,
        strUtilization,
        factor           = 1,
        factor_potency   = 1024,
        unit             = ' B';
    
    if (l_num_disks % 2 != 0 && (p_raidtype == '1' || p_raidtype == '10')) {
        l_message = ' Disk amount must be a multiple of 2.';
        l_num_disks--;
    }
    
    l_diskspace = (l_num_disks * l_diskspace_each);
    
    if (p_space > 1024) {
        factor = factor_potency;
        unit = ' KB';
        if (p_space > (factor_potency = Math.pow(1024, 2))) {
            factor = factor_potency;
            unit = ' MB';
            if (p_space > (factor_potency = Math.pow(1024, 3))) {
                factor = factor_potency;
                unit = ' GB';
                if (p_space > (factor_potency = Math.pow(1024, 4))) {
                    factor = factor_potency;
                    unit = ' TB';
                }
            }
        }
    }
    
    switch (p_raidtype) {
        case '1':
            if (l_num_disks >= 2) {
                strUtilization = (l_diskspace / factor) / 2;
                l_space = parseInt(strUtilization * 1000000) / 1000000;
            }
            break;
        case '10':
            if (l_num_disks >= 4) {
                strUtilization = (l_diskspace / factor) / 2;
                l_space = parseInt(strUtilization * 1000000) / 1000000;
            }
            break;
        case '2':
            
            break;
        case '3':
            if (l_num_disks >= 3) {
                strUtilization = ((l_diskspace / factor) * ((parseInt(((l_num_disks - 1) / l_num_disks) * 10000) / 100) / 100));
                l_space = parseInt(strUtilization * 1000000) / 1000000;
            }
            break;
        case '4':
            if (l_num_disks >= 3) {
                strUtilization = ((l_diskspace / factor) * ((parseInt(((l_num_disks - 1) / l_num_disks) * 10000) / 100) / 100));
                
                l_space = parseInt(strUtilization * 1000000) / 1000000;
            }
            break;
        case '5':
            if (l_num_disks >= 3) {
                //strUtilization = (l_diskspace * ((parseInt(((l_num_disks-1)/l_num_disks)*10000)/100)/100));
                strUtilization = ((l_diskspace / factor) / parseInt(l_num_disks)) * (parseInt(l_num_disks) - 1);
                
                l_space = parseInt(strUtilization * 1000000) / 1000000;
            }
            break;
        case '6':
            if (l_num_disks >= 4) {
                strUtilization = ((l_diskspace / factor) * ((parseInt(((l_num_disks - 2) / l_num_disks) * 10000) / 100) / 100));
                
                l_space = parseInt(strUtilization * 1000000) / 1000000;
                
            }
            break;
        case '0':
            if (l_num_disks >= 2) {
                strUtilization = l_diskspace;
                
                l_space = parseInt(strUtilization * 1000000) / 1000000 + l_message;
            }
            break;
    }
    
    if ($(p_target)) {
        if (l_space != 0 && isNumeric(l_space)) {
            $(p_target).update(l_space + unit);
            
        } else {
            $(p_target).update('0.00');
        }
    }
    
    if (l_space != 0 && isNumeric(l_space)) {
        return l_space;
    }
}