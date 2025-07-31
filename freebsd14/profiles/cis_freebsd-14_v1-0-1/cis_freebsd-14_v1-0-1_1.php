<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.1.pdf
// =============================================================
return [
    [ 'id' => '1', 'title' => 'Initial Setup', 'type' => 'header' ],

    [ 'id' => '1.1', 'title' => 'Filesystem & Bootloader', 'type' => 'header' ],

    [ 'id' => '1.1.1', 'title' => 'Configure Filesystem Kernel Modules', 'type' => 'header' ],

    [ 'id' => '1.1.1.1', 'title' => 'Ensure ext2fs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    MODULE_NAME="ext2fs"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if kldstat -q -m "$MODULE_NAME"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Kernel module '$MODULE_NAME' is currently loaded.")

        module_info=$(kldstat -v -m "$MODULE_NAME")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Module details:")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$module_info")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Kernel module '$MODULE_NAME' is not loaded.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To unload the module and prevent it from loading on boot, run:"
        printf '%s\n' "# kldunload -f $MODULE_NAME"
        printf '%s\n' "# printf \"${MODULE_NAME}_load=\\\"NO\\\"\\n\" >> /boot/loader.conf"
    fi
}
BASH
    ],

    [ 'id' => '1.1.1.2', 'title' => 'Ensure msdosfs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    MODULE_NAME="msdosfs"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if kldstat -q -m "$MODULE_NAME"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Kernel module '$MODULE_NAME' is currently loaded.")

        module_info=$(kldstat -v -m "$MODULE_NAME")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Module details:")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$module_info")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Kernel module '$MODULE_NAME' is not loaded.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To unload the module and prevent it from loading on boot, run:"
        printf '%s\n' "# kldunload -f $MODULE_NAME"
        printf '%s\n' "# printf \"${MODULE_NAME}_load=\\\"NO\\\"\\n\" >> /boot/loader.conf"
    fi
}
BASH
    ],

    [ 'id' => '1.1.1.3', 'title' => 'Ensure zfs kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    MODULE_NAME="zfs"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if kldstat -q -m "$MODULE_NAME"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Kernel module '$MODULE_NAME' is currently loaded.")

        module_info=$(kldstat -v -m "$MODULE_NAME")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Module details:")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$module_info")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Kernel module '$MODULE_NAME' is not loaded.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "IF the module is available in the running kernel:"
        printf '%s\n' "Stop the $MODULE_NAME service "
        printf '%s\n' " # service $MODULE_NAME stop"
        printf '%s\n' "Disable the $MODULE_NAME service"
        printf '%s\n' " # sysrc $MODULE_NAME_enable=NO"
        printf '%s\n' "Disable loading the kernel module" 
        printf '%s\n' " # kldunload -f $MODULE_NAME"
        printf '%s\n' "Unload $MODULE_NAME from the kernel"     
        printf '%s\n' " # printf \"${MODULE_NAME}_load=\\\"NO\\\"\\n\" >> /boot/loader.conf"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2', 'title' => 'Configure Filesystem Partitions', 'type' => 'header' ],

    [ 'id' => '1.1.2.1', 'title' => 'Configure /tmp', 'type' => 'header' ],

    [ 'id' => '1.1.2.1.1', 'title' => 'Ensure /tmp is a separate partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/tmp\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /tmp is currently mounted as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /tmp is not currently mounted as a separate partition.")
    fi

    tmpmfs_status=$(sysrc -n tmpmfs)
    fstab_entry=$(grep -E '^\S+\s+/tmp\s' /etc/fstab)

    if [ "$tmpmfs_status" = "YES" ] || [ -n "$fstab_entry" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /tmp is configured to be mounted on boot.")
        if [ -n "$fstab_entry" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found fstab entry: $fstab_entry")
        else
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found rc.conf entry: tmpmfs=$tmpmfs_status")
        fi
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /tmp is not configured to be mounted on boot (via fstab or rc.conf).")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To configure /tmp as a tmpfs filesystem, choose one of the following methods:"
        printf '%s\n' "1. Recommended (rc.conf):"
        printf '%s\n' "   # sysrc tmpmfs=\"YES\""
        printf '%s\n' "   # sysrc tmpsize=\"2g\"  (Optional)"
        printf '%s\n' "2. Advanced (fstab):"
        printf '%s\n' "   Add to /etc/fstab: tmpfs /tmp tmpfs rw,nosuid,noexec,size=2g 0 0"
        printf '%s\n' "Then apply the changes immediately with: # service tmp restart"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.1.2', 'title' => 'Ensure nosuid option set on /tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/tmp\s')
    
    if [ -z "$mount_details" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - /tmp is not configured as a separate partition."
    else
        fs_type=$(echo "$mount_details" | awk '{print $3}')
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - /tmp is a separate partition of type '$fs_type'.")

        if [ "$fs_type" = "tmpfs" ]; then
            # If the type is tmpfs, this specific check is not applicable
            printf '\n%s\n' "- Audit Result:" "  ** MANUAL **"
            printf '%s\n' "  - /tmp is a 'tmpfs' filesystem. Mount options for tmpfs are handled by rc.conf or fstab directly for that type."
        else
            mount_options=$(echo "$mount_details" | awk '{print $4}')
            
            if echo "$mount_options" | grep -q "nosuid"; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /tmp partition.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /tmp partition.")
            fi
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -n "$OUTPUT_PASS" ] && [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    elif [ -n "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To add the 'nosuid' option to the /tmp mount in /etc/fstab,"
        printf '%s\n' "find the line for /tmp and add 'nosuid' to its options."
        printf '%s\n' "Example: /dev/da1p3   /tmp    ufs    rw,nosuid   2   2"
        printf '%s\n' "Then, remount the partition with the new option:"
        printf '%s\n' "# mount -u -o nosuid /tmp"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.1.3', 'title' => 'Ensure noexec option set on /tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/tmp\s')
    
    if [ -z "$mount_details" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - /tmp is not configured as a separate partition."
    else
        fs_type=$(echo "$mount_details" | awk '{print $3}')
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - /tmp is a separate partition of type '$fs_type'.")

        if [ "$fs_type" = "tmpfs" ]; then
            printf '\n%s\n' "- Audit Result:" "  ** MANUAL **"
            printf '%s\n' "  - /tmp is a 'tmpfs' filesystem. Mount options for tmpfs are handled by rc.conf or fstab directly for that type."
        else
            mount_options=$(echo "$mount_details" | awk '{print $4}')
            
            if echo "$mount_options" | grep -q "noexec"; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'noexec' option is correctly set on the /tmp partition.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'noexec' option is NOT set on the /tmp partition.")
            fi
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -n "$OUTPUT_PASS" ] && [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    elif [ -n "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To add the 'noexec' option to the /tmp mount in /etc/fstab,"
        printf '%s\n' "find the line for /tmp and add 'noexec' to its options."
        printf '%s\n' "Example: /dev/da1p3   /tmp    ufs    rw,nosuid,noexec   2   2"
        printf '%s\n' "Then, remount the partition with the new option:"
        printf '%s\n' "# mount -u -o noexec /tmp"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.2', 'title' => 'Configure /home', 'type' => 'header' ],

    [ 'id' => '1.1.2.2.1', 'title' => 'Ensure separate partition exists for /home', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/home\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /home is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /home is not configured as a separate partition.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For new installations, create a separate partition for /home during setup."
        printf '%s\n' "For existing systems, create a new partition and add an entry for /home in /etc/fstab."
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.2.2', 'title' => 'Ensure nosuid option set on /home partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/home\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /home is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /home is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "nosuid"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /home partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /home partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /home is a separate partition in /etc/fstab and includes the 'nosuid' option."
        printf '%s\n' "Example: <device> /home    <fstype>     rw,nosuid  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.3', 'title' => 'Configure /var', 'type' => 'header' ],

    [ 'id' => '1.1.2.3.1', 'title' => 'Ensure separate partition exists for /var', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var is not configured as a separate partition.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For new installations, create a separate partition for /var during setup."
        printf '%s\n' "For existing systems, create a new partition and add an entry for /var in /etc/fstab."
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.3.2', 'title' => 'Ensure nosuid option set on /var partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "nosuid"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /var partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /var partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var is a separate partition in /etc/fstab and includes the 'nosuid' option."
        printf '%s\n' "Example: <device> /var    <fstype>     rw,nosuid  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.4', 'title' => 'Configure /var/tmp', 'type' => 'header' ],

    [ 'id' => '1.1.2.4.1', 'title' => 'Ensure separate partition exists for /var/tmp', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/tmp\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/tmp is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/tmp is not configured as a separate partition.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For new installations, create a separate partition for /var/tmp during setup."
        printf '%s\n' "For existing systems, create a new partition and add an entry for /var/tmp in /etc/fstab."
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.4.2', 'title' => 'Ensure nosuid option set on /var/tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/tmp\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/tmp is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/tmp is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "nosuid"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /var/tmp partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /var/tmp partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/tmp is a separate partition in /etc/fstab and includes the 'nosuid' option."
        printf '%s\n' "Example: <device> /var/tmp    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.4.3', 'title' => 'Ensure noexec option set on /var/tmp partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/tmp\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/tmp is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/tmp is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "noexec"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'noexec' option is correctly set on the /var/tmp partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'noexec' option is NOT set on the /var/tmp partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/tmp is a separate partition in /etc/fstab and includes the 'noexec' option."
        printf '%s\n' "Example: <device> /var/tmp    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.5', 'title' => 'Configure /var/log', 'type' => 'header' ],

    [ 'id' => '1.1.2.5.1', 'title' => 'Ensure separate partition exists for /var/log', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/log\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/log is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/log is not configured as a separate partition.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For new installations, create a separate partition for /var/log during setup."
        printf '%s\n' "For existing systems, create a new partition and add an entry for /var/log in /etc/fstab."
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.5.2', 'title' => 'Ensure nosuid option set on /var/log partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/log\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/log is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/log is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "nosuid"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /var/log partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /var/log partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/log is a separate partition in /etc/fstab and includes the 'nosuid' option."
        printf '%s\n' "Example: <device> /var/log    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.5.3', 'title' => 'Ensure noexec option set on /var/log partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/log\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/log is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/log is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "noexec"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'noexec' option is correctly set on the /var/log partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'noexec' option is NOT set on the /var/log partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/log is a separate partition in /etc/fstab and includes the 'noexec' option."
        printf '%s\n' "Example: <device> /var/log    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.6', 'title' => 'Configure /var/audit', 'type' => 'header' ],

    [ 'id' => '1.1.2.6.1', 'title' => 'Ensure separate partition exists for /var/audit', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/audit\s')
    
    if [ -n "$mount_details" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/audit is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/audit is not configured as a separate partition.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For new installations, create a separate partition for /var/audit during setup."
        printf '%s\n' "For existing systems, create a new partition and add an entry for /var/audit in /etc/fstab."
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.6.2', 'title' => 'Ensure nosuid option set on /var/audit partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/audit\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/audit is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/audit is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "nosuid"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'nosuid' option is correctly set on the /var/audit partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nosuid' option is NOT set on the /var/audit partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/audit is a separate partition in /etc/fstab and includes the 'nosuid' option."
        printf '%s\n' "Example: <device> /var/audit    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.1.2.6.3', 'title' => 'Ensure noexec option set on /var/audit partition', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    mount_details=$(mount -p | grep -E '\s/var/audit\s')
    
    if [ -z "$mount_details" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - /var/audit is not configured as a separate partition.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - /var/audit is configured as a separate partition.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Mount details: $mount_details")
        
        mount_options=$(echo "$mount_details" | awk '{print $4}')
        
        if echo "$mount_options" | grep -q "noexec"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'noexec' option is correctly set on the /var/audit partition.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'noexec' option is NOT set on the /var/audit partition.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /var/audit is a separate partition in /etc/fstab and includes the 'noexec' option."
        printf '%s\n' "Example: <device> /var/audit    <fstype>     rw,nosuid,noexec  0 0"
    fi
}
BASH
    ],

    [ 'id' => '1.2', 'title' => 'Configure Software and Patch Management', 'type' => 'header' ],

    [ 'id' => '1.2.1', 'title' => 'Ensure update server certificate key fingerprints are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    
    TMP_DIR=$(mktemp -d)
    if [ ! -d "$TMP_DIR" ]; then
        echo "** FAIL **: Could not create temporary directory."
        exit 1
    fi
    
    REMOTE_PUB_KEY_URL="http://update.freebsd.org/14.0-RELEASE/amd64/pub.ssl"
    REMOTE_LATEST_URL="http://update.freebsd.org/14.0-RELEASE/amd64/latest.ssl"
    LOCAL_CONFIG="/etc/freebsd-update.conf"

    echo "Fetching files from FreeBSD update server..."
    fetch -o "$TMP_DIR/pub.ssl" "$REMOTE_PUB_KEY_URL" >/dev/null 2>&1
    fetch_pub_result=$?
    
    fetch -o "$TMP_DIR/latest.ssl" "$REMOTE_LATEST_URL" >/dev/null 2>&1
    fetch_latest_result=$?

    if [ $fetch_pub_result -ne 0 ] || [ $fetch_latest_result -ne 0 ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Failed to download necessary files from the update server. Check network connection.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Successfully downloaded verification files.")
        
        openssl rsautl -pubin -inkey "$TMP_DIR/pub.ssl" -verify < "$TMP_DIR/latest.ssl" >/dev/null 2>&1
        if [ $? -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Signature of 'latest.ssl' is valid.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Signature verification of 'latest.ssl' failed.")
        fi

        remote_hash=$(sha256 -q "$TMP_DIR/pub.ssl")
        local_hash=$(grep KeyPrint "$LOCAL_CONFIG" | awk '{print $2}')

        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Remote Key Hash: $remote_hash")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Local Key Hash:  $local_hash")

        if [ "$remote_hash" = "$local_hash" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Local KeyPrint in '$LOCAL_CONFIG' matches the official server key.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Local KeyPrint in '$LOCAL_CONFIG' does NOT match the official server key.")
        fi
    fi

    rm -rf "$TMP_DIR"

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A mismatch indicates a potential issue. Common causes are:"
        printf '%s\n' "  - Incorrect system date and time."
        printf '%s\n' "  - An outdated FreeBSD version."
        printf '%s\n' "Please investigate the cause before taking action."
    fi
}
BASH
    ],

    [ 'id' => '1.2.2', 'title' => 'Ensure package manager repositories are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Please review the following repository configurations to ensure they match your site policy."
    
    printf '\n%s\n' "--- Active Repositories (from pkg -vv) ---"
    pkg -vv | sed '1,/^Repositories/d'
    printf '%s\n' "------------------------------------------"
    
    printf '\n%s\n' "--- Configuration Files for Review ---"
    if [ -d "/etc/pkg/" ]; then
        printf '%s\n' "Found config files in /etc/pkg/:"
        ls /etc/pkg/*.conf 2>/dev/null
    fi
    if [ -d "/usr/local/etc/pkg/repos/" ]; then
        printf '\n%s\n' "Found repo files in /usr/local/etc/pkg/repos/:"
        ls /usr/local/etc/pkg/repos/*.conf 2>/dev/null
    fi
    printf '%s\n' "------------------------------------"

    printf '\n%s\n' "** MANUAL ** Required: Manually review the output above against your site policy."
}
BASH
    ],

    [ 'id' => '1.2.3', 'title' => 'Ensure updates, patches, and additional security software are installed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Checking for available updates for the base system and installed packages."
    
    printf '\n%s\n' "--- Base System Update Check ---"
    updates_output=$(sudo freebsd-update fetch --not-running-from-cron)
    
    if echo "$updates_output" | grep -q "No updates needed"; then
        printf '%s\n' "No updates needed for the base system."
    else
        printf '%s\n' "Updates are available for the base system. Output from 'freebsd-update fetch':"
        echo "$updates_output"
    fi
    printf '%s\n' "--------------------------------"

    printf '\n%s\n' "--- Package Update Check ---"
    pkg_updates_output=$(pkg upgrade -n)

    if echo "$pkg_updates_output" | grep -q "Your packages are up to date"; then
         printf '%s\n' "No updates needed for installed packages."
    else
        printf '%s\n' "Updates are available for installed packages. Output from 'pkg upgrade -n':"
        echo "$pkg_updates_output"
    fi
    printf '%s\n' "----------------------------"

    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "To install base system updates, run:"
    printf '%s\n' "# sudo freebsd-update fetch install"
    printf '%s\n' ""
    printf '%s\n' "To install package updates, run:"
    printf '%s\n' "# sudo pkg update && sudo pkg upgrade"

    printf '\n%s\n' "** MANUAL ** Required: Manually review the output above and apply updates according to your site policy."
}
BASH
    ],

    [ 'id' => '1.3', 'title' => 'Configure Secure Boot Settings ', 'type' => 'header'],

    [ 'id' => '1.3.1', 'title' => 'Ensure bootloader password is set', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/boot/loader.conf"

    if grep -q "^password=" "$CONFIG_FILE"; then
        password_line=$(grep "^password=" "$CONFIG_FILE")
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - A bootloader password is set in '$CONFIG_FILE'.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found entry: $password_line")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - WARNING: The password is saved in plaintext.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Bootloader password is not set in '$CONFIG_FILE'.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        # --- Remediation Section ---
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Add a 'password' line to the '$CONFIG_FILE' file."
        printf '%s\n' "Example:"
        printf '%s\n' "password=YourSecurePasswordHere"
    fi
}
BASH
    ],

    [ 'id' => '1.3.2', 'title' => 'Ensure permissions on bootloader config are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    check_file_permissions() {
        file_to_check=$1
        
        if [ ! -f "$file_to_check" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - File '$file_to_check' does not exist, skipping check.")
            return
        fi

        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $file_to_check")
        
        perms=$(stat -f '%Lp' "$file_to_check")
        owner=$(stat -f '%Su' "$file_to_check")
        group=$(stat -f '%Sg' "$file_to_check")

        pass_flag=1

        case "$perms" in
            [0-5]?? | 600)
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_to_check: Permissions '$perms' are compliant (600 or less).")
                ;;
            *)
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_to_check: Permissions are '$perms', but should be 600 or more restrictive.")
                pass_flag=0
                ;;
        esac

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_to_check: Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_to_check: Owner is '$owner', but should be 'root'.")
            pass_flag=0
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_to_check: Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_to_check: Group is '$group', but should be 'wheel'.")
            pass_flag=0
        fi
    }

    check_file_permissions "/boot/loader.conf"
    check_file_permissions "/boot/loader.conf.local"


    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set correct permissions on bootloader files:"
        printf '%s\n' "# chown root:wheel /boot/loader.conf"
        printf '%s\n' "# chmod u-x,go-rwx /boot/loader.conf"
    fi
}
BASH
    ],

    [ 'id' => '1.4', 'title' => 'Configure Additional Process Hardening', 'type' => 'header' ],

    [ 'id' => '1.4.1', 'title' => 'Ensure address space layout randomization (ASLR) is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="kern.elf64.aslr.enable"
    EXPECTED_VALUE="1"
    CONFIG_FILE="/etc/sysctl.conf"

    running_value=$(sysctl -n "$PARAM_NAME")
    
    if [ "$running_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running configuration for '$PARAM_NAME' is correctly set to '1'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running configuration for '$PARAM_NAME' is '$running_value', but should be '1'.")
    fi

    # Use grep to find the last active setting for the parameter in the config file
    config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)

    if [ -n "$config_setting" ]; then
        config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
        if [ "$config_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent configuration for '$PARAM_NAME' is correctly set to '1' in '$CONFIG_FILE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent configuration for '$PARAM_NAME' is '$config_value' in '$CONFIG_FILE', but should be '1'.")
        fi
    else
         OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To enable ASLR, add the following line to '$CONFIG_FILE':"
        printf '%s\n' "${PARAM_NAME}=${EXPECTED_VALUE}"
        printf '%s\n' "Then, apply the setting to the running configuration:"
        printf '%s\n' "# sysctl ${PARAM_NAME}=${EXPECTED_VALUE}"
    fi
}
BASH
    ],

    [ 'id' => '1.4.2', 'title' => 'Ensure core dump backtraces are disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="savecore_enable"

    # --- Check if savecore_enable is set to YES ---
    savecore_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $savecore_status")

    if [ "$savecore_status" = "YES" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is enabled.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is not enabled ('$savecore_status').")
    fi

    # --- Display Results ---
    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        # --- Remediation Section ---
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To disable the savecore service, run:"
        printf '%s\n' "# service savecore onestop"
        printf '%s\n' "# service savecore onedisable"
    fi
}
BASH
    ],

    [ 'id' => '1.4.3', 'title' => 'Ensure core dump storage is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="dumpdev"
    EXPECTED_VALUE="NO"

    dumpdev_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $dumpdev_status")

    if [ "$dumpdev_status" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is set to '$dumpdev_status', but should be '$EXPECTED_VALUE'.")
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To disable core dump storage, run:"
        printf '%s\n' "# sysrc ${RC_VAR}=${EXPECTED_VALUE}"
    fi
}
BASH
    ],

    [ 'id' => '1.5', 'title' => 'Mandatory Access Control', 'type' => 'header',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    # This is an informational section with no direct audit steps.
    # The presence of this header in the report indicates that the administrator
    # should review and understand the implications of implementing MAC.
    echo "** REVIEW **: This is an informational section."
    echo "  - Mandatory Access Control (MAC) is a powerful security feature in FreeBSD."
    echo "  - Its implementation is highly dependent on site-specific security policies."
    echo "  - Review the CIS Benchmark document for section 1.5 to understand the impact before enabling any MAC modules."
}
BASH
    ],

    [ 'id' => '1.6', 'title' => 'Configure Command Line Warning Banners', 'type' => 'header'],

    [ 'id' => '1.6.1', 'title' => 'Ensure message of the day is configured properly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    MOTD_FILE="/etc/motd"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if [ ! -f "$MOTD_FILE" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - File '$MOTD_FILE' does not exist."
    else
        os_version=$(freebsd-version -u | cut -d- -f1,2)
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking for OS version string '$os_version' in $MOTD_FILE.")

        if grep -q -i "$os_version" "$MOTD_FILE"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - MOTD file contains OS version information, which is discouraged.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - MOTD file does not contain OS version information.")
        fi
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  --- Current Content of $MOTD_FILE ---")
        motd_content=$(cat "$MOTD_FILE")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$motd_content")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  ------------------------------------")


        if [ -n "$OUTPUT_INFO" ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "$OUTPUT_INFO"
        fi

        if [ -z "$OUTPUT_FAIL" ]; then
            printf '\n%s\n' "- Audit Result:" "  **  MANUAL **"
            printf '%s\n' "$OUTPUT_PASS"
            printf '%s\n' "  - Please manually verify the content above against your site policy."
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "$OUTPUT_FAIL"

            if [ -n "$OUTPUT_PASS" ]; then
                printf '\n%s\n' "- Correctly set:"
                printf '%s\n' "$OUTPUT_PASS"
            fi

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Edit '$MOTD_FILE' to remove OS-specific information and ensure the content"
            printf '%s\n' "matches your site's legal policy."
            printf '%s\n' "Alternatively, disable the motd service if not required:"
            printf '%s\n' "# service motd stop && service motd disable"
        fi
    fi
}
BASH
    ],

    [ 'id' => '1.6.2', 'title' => 'Ensure local login warning banner is configured properly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    ISSUE_FILE="/etc/issue"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if [ ! -f "$ISSUE_FILE" ]; then
        printf '\n%s\n' "- Audit Result:" "  **  FAIL **"
        printf '%s\n' "  - File '$ISSUE_FILE' does not exist. A banner is not configured."
    else
        os_version=$(freebsd-version -u | cut -d- -f1,2)
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking for OS version string '$os_version' in $ISSUE_FILE.")

        if grep -q -i "$os_version" "$ISSUE_FILE"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Banner file contains OS version information, which is discouraged.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Banner file does not contain OS version information.")
        fi
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  --- Current Content of $ISSUE_FILE ---")
        issue_content=$(cat "$ISSUE_FILE")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$issue_content")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  ------------------------------------")

        if [ -n "$OUTPUT_INFO" ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "$OUTPUT_INFO"
        fi

        if [ -z "$OUTPUT_FAIL" ]; then
            printf '\n%s\n' "- Audit Result:" "  **  PASS **"
            printf '%s\n' "$OUTPUT_PASS"
            printf '%s\n' "  - Please manually verify the content above against your site policy."
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "$OUTPUT_FAIL"
        fi
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit '$ISSUE_FILE' to remove OS-specific information and ensure the content matches your site's legal policy."
        printf '%s\n' "Example:"
        printf '%s\n' "# echo 'Authorized users only. All activity may be monitored and reported.' > /etc/issue"
    fi
}
BASH
    ],

    [ 'id' => '1.6.3', 'title' => 'Ensure remote login warning banner is configured properly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    ISSUE_NET_FILE="/etc/issue.net"
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if [ ! -f "$ISSUE_NET_FILE" ]; then
        # If the file doesn't exist, it's not a failure, but it's not explicitly configured.
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - File '$ISSUE_NET_FILE' does not exist. A remote banner is not configured."
    else
        os_version=$(freebsd-version -u | cut -d- -f1,2)
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking for OS version string '$os_version' in $ISSUE_NET_FILE.")

        if grep -q -i "$os_version" "$ISSUE_NET_FILE"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Banner file contains OS version information, which is discouraged.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Banner file does not contain OS version information.")
        fi
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  --- Current Content of $ISSUE_NET_FILE ---")
        issue_net_content=$(cat "$ISSUE_NET_FILE")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$issue_net_content")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  ------------------------------------")

        if [ -n "$OUTPUT_INFO" ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "$OUTPUT_INFO"
        fi

        if [ -z "$OUTPUT_FAIL" ]; then
            printf '\n%s\n' "- Audit Result:" "  ** PASS **"
            printf '%s\n' "$OUTPUT_PASS"
            printf '%s\n' "  - Please manually verify the content above against your site policy."
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "$OUTPUT_FAIL"
        fi
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit '$ISSUE_NET_FILE' to remove OS-specific information and ensure the content matches your site's legal policy."
        printf '%s\n' "Example:"
        printf '%s\n' "# echo 'Authorized users only. All activity may be monitored and reported.' > /etc/issue.net"
    fi
}
BASH
    ],

    [ 'id' => '1.6.4', 'title' => 'Ensure access to /etc/motd is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    MOTD_FILE="/etc/motd"

    if [ ! -f "$MOTD_FILE" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - File '$MOTD_FILE' does not exist."
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $MOTD_FILE")
        
        perms=$(stat -f '%Lp' "$MOTD_FILE")
        owner=$(stat -f '%Su' "$MOTD_FILE")
        group=$(stat -f '%Sg' "$MOTD_FILE")

        pass_flag=1

        case "$perms" in
            [0-5]?? | 6[0-4][0-4] | 644)
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant (644 or less).")
                ;;
            *)
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be 644 or more restrictive.")
                pass_flag=0
                ;;
        esac

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
            pass_flag=0
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
            pass_flag=0
        fi

        if [ -n "$OUTPUT_INFO" ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "$OUTPUT_INFO"
        fi

        if [ -z "$OUTPUT_FAIL" ]; then
            printf '\n%s\n' "- Audit Result:" "  ** PASS **"
            printf '%s\n' "$OUTPUT_PASS"
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "$OUTPUT_FAIL"

            if [ -n "$OUTPUT_PASS" ]; then
                printf '\n%s\n' "- Correctly set:"
                printf '%s\n' "$OUTPUT_PASS"
            fi

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Run the following commands to set correct permissions:"
            printf '%s\n' "# chown root:wheel /etc/motd"
            printf '%s\n' "# chmod u-x,go-wx /etc/motd"
        fi
    fi
}
BASH
    ], 

    [ 'id' => '1.6.5', 'title' => 'Ensure access to /etc/issue is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    ISSUE_FILE="/etc/issue"

    if [ ! -f "$ISSUE_FILE" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - File '$ISSUE_FILE' does not exist."
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $ISSUE_FILE")
        
        perms=$(stat -f '%Lp' "$ISSUE_FILE")
        owner=$(stat -f '%Su' "$ISSUE_FILE")
        group=$(stat -f '%Sg' "$ISSUE_FILE")

        pass_flag=1

        case "$perms" in
            [0-5]?? | 6[0-4][0-4] | 644)
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant (644 or less).")
                ;;
            *)
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be 644 or more restrictive.")
                pass_flag=0
                ;;
        esac

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
            pass_flag=0
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
            pass_flag=0
        fi

        if [ -n "$OUTPUT_INFO" ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "$OUTPUT_INFO"
        fi

        if [ -z "$OUTPUT_FAIL" ]; then
            printf '\n%s\n' "- Audit Result:" "  ** PASS **"
            printf '%s\n' "$OUTPUT_PASS"
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "$OUTPUT_FAIL"

            if [ -n "$OUTPUT_PASS" ]; then
                printf '\n%s\n' "- Correctly set:"
                printf '%s\n' "$OUTPUT_PASS"
            fi

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Run the following commands to set correct permissions:"
            printf '%s\n' "# chown root:wheel /etc/issue"
            printf '%s\n' "# chmod u-x,go-wx /etc/issue"
        fi
    fi
}
BASH   
    ]
];
