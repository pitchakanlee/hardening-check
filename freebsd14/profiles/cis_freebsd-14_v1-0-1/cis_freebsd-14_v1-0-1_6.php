<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.6.pdf
// =============================================================
return [
    [ 'id' => '6', 'title' => 'System Maintenance', 'type' => 'header'],

    [ 'id' => '6.1', 'title' => 'System File Permissions', 'type' => 'header'],

    [ 'id' => '6.1.1', 'title' => 'Ensure permissions on /etc/passwd are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/passwd"

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        perms_masked=$(($perms & 022))
        if [ "$perms_masked" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant (644 or more restrictive).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be 644 or more restrictive.")
        fi

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/passwd:"
        printf '%s\n' "# chown root:wheel /etc/passwd"
        printf '%s\n' "# chmod u-x,go-wx /etc/passwd"
    fi
}
BASH
    ],

    [ 'id' => '6.1.2', 'title' => 'Ensure permissions on /etc/group are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/group"

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        perms_masked=$(($perms & 022))
        if [ "$perms_masked" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant (644 or more restrictive).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be 644 or more restrictive.")
        fi

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/group:"
        printf '%s\n' "# chown root:wheel /etc/group"
        printf '%s\n' "# chmod u-x,go-wx /etc/group"
    fi
}
BASH
    ],

    [ 'id' => '6.1.3', 'title' => 'Ensure permissions on /etc/master.passwd are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/master.passwd"
    EXPECTED_PERMS="0" # The audit requires the file to be mode 000

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        if [ "$perms" -eq "$EXPECTED_PERMS" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be '$EXPECTED_PERMS'.")
        fi

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/master.passwd:"
        printf '%s\n' "# chown root:wheel /etc/master.passwd"
        printf '%s\n' "# chmod 000 /etc/master.passwd"
    fi
}
BASH
    ],

    [ 'id' => '6.1.4', 'title' => 'Ensure permissions on /etc/shells are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/shells"

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        perms_masked=$(($perms & 022))
        if [ "$perms_masked" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms' are compliant (644 or more restrictive).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms', but should be 644 or more restrictive.")
        fi

        if [ "$owner" = "root" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Owner is 'root'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Owner is '$owner', but should be 'root'.")
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Group is '$group', but should be 'wheel'.")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/shells:"
        printf '%s\n' "# chown root:wheel /etc/shells"
        printf '%s\n' "# chmod u-x,go-wx /etc/shells"
    fi
}
BASH
    ],

    [ 'id' => '6.1.5', 'title' => 'Ensure world writable files and directories are secured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Searching for world-writable files (this may take a while)...")
    writable_files=$(find / -path /tmp -prune -o -path /proc -prune -o -type f -perm -0002 -print)

    if [ -n "$writable_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found world-writable files:")
        echo "$writable_files" | while IFS= read -r file; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No world-writable files were found.")
    fi

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Searching for world-writable directories without the sticky bit...")
    writable_dirs=$(find / -path /tmp -prune -o -path /proc -prune -o -type d -perm -0002 ! -perm -1000 -print)

    if [ -n "$writable_dirs" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found world-writable directories without the sticky bit:")
        echo "$writable_dirs" | while IFS= read -r dir; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $dir")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No world-writable directories without the sticky bit were found.")
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
        printf '%s\n' "For world-writable files, remove the 'other' write permission:"
        printf '%s\n' "# chmod o-w /path/to/file"
        printf '%s\n' "For world-writable directories, add the sticky bit:"
        printf '%s\n' "# chmod +t /path/to/directory"
    fi
}
BASH
    ],

    [ 'id' => '6.1.6', 'title' => 'Ensure no unowned or ungrouped files or directories exist', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Searching for unowned/ungrouped files and directories (this may take a while)...")
    
    offending_files=$(find / -xdev \( -nouser -o -nogroup \) -print 2>/dev/null)

    if [ -n "$offending_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found files or directories without a valid owner or group:")
        
        echo "$offending_files" | while IFS= read -r file; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No unowned or ungrouped files or directories were found.")
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
        printf '%s\n' "For each file/directory listed, assign a valid owner and group."
        printf '%s\n' "Example:"
        printf '%s\n' "# chown root:wheel /path/to/offending_file"
    fi
}
BASH
    ],

    [ 'id' => '6.1.7', 'title' => 'Ensure SUID and SGID files are reviewed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Searching for all SUID and SGID files on local filesystems."
    printf '%s\n' "Please review the lists below to ensure no unauthorized files exist."
    printf '%s\n' "This may take a long time to complete..."
    
    printf '\n%s\n' "--- SUID Files Found ---"
    find / -xdev -type f -perm -4000 -print
    printf '%s\n' "------------------------"
    
    printf '\n%s\n' "--- SGID Files Found ---"
    find / -xdev -type f -perm -2000 -print
    printf '%s\n' "------------------------"

    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "Review the files listed above. If any are not authorized"
    printf '%s\n' "by your site policy, remove the SUID/SGID bit."
    printf '%s\n' "Example to remove the SUID bit:"
    printf '%s\n' "# chmod u-s /path/to/unauthorized/file"
    
    printf '\n%s\n' "** MANUAL ** Required: Manually review the file lists above."
}
BASH
    ],

    [ 'id' => '6.2', 'title' => 'Local User and Group Settings', 'type' => 'header'],

    [ 'id' => '6.2.1', 'title' => 'Ensure accounts in /etc/master.passwd use shadowed passwords', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    MASTER_PASSWD_FILE="/etc/master.passwd"

    non_shadowed=$(awk -F: '($2 == "*" ) { print $1 }' "$MASTER_PASSWD_FILE")

    if [ -n "$non_shadowed" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following account(s) do not have a shadowed password and should be investigated:")
        
        echo "$non_shadowed" | while IFS= read -r user; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $user")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All accounts in '$MASTER_PASSWD_FILE' are using shadowed passwords.")
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
        printf '%s\n' "For each account listed, investigate its purpose."
        printf '%s\n' "If the account is not needed, consider removing it."
        printf '%s\n' "If it is needed, ensure it has a proper password set or is locked with 'pw lock <username>'."
    fi
}
BASH
    ],

    [ 'id' => '6.2.2', 'title' => 'Ensure /etc/master.passwd password fields are not empty', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    MASTER_PASSWD_FILE="/etc/master.passwd"

    empty_password_accounts=$(awk -F: '($2 == "") { print $1 }' "$MASTER_PASSWD_FILE")

    if [ -n "$empty_password_accounts" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following account(s) have an empty password field:")
        
        echo "$empty_password_accounts" | while IFS= read -r user; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $user")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No accounts with empty password fields were found in '$MASTER_PASSWD_FILE'.")
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
        printf '%s\n' "For each account listed, lock the account until a secure password can be set."
        printf '%s\n' "Example:"
        printf '%s\n' "# pw lock <username>"
    fi
}
BASH
    ],

    [ 'id' => '6.2.3', 'title' => 'Ensure all groups in /etc/passwd exist in /etc/group', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    non_existent_gids=$(awk -F: '{print $4}' /etc/passwd | sort -u | while read -r gid; do
        if ! grep -q -E "^[^:]+:[^:]+:$gid:" /etc/group; then
            echo "$gid"
        fi
    done)

    if [ -n "$non_existent_gids" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following GID(s) are referenced in /etc/passwd but do not exist in /etc/group:")
        
        echo "$non_existent_gids" | while IFS= read -r gid; do
            users=$(awk -F: -v g="$gid" '$4==g {print $1}' /etc/passwd | xargs)
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - GID '$gid' (used by user(s): $users)")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All group IDs in /etc/passwd exist in /etc/group.")
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
        printf '%s\n' "For each GID listed, either create a corresponding group"
        printf '%s\n' "or change the user's GID to an existing group."
    fi
}
BASH
    ],

    [ 'id' => '6.2.4', 'title' => 'Ensure no duplicate UIDs exist', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PASSWD_FILE="/etc/passwd"

    duplicate_uids=$(cut -f3 -d: "$PASSWD_FILE" | sort -n | uniq -d)
    
    non_compliant_uids=""
    for uid in $duplicate_uids; do
        if [ "$uid" -ne 0 ]; then
            non_compliant_uids="$non_compliant_uids $uid"
        fi
    done

    if [ -n "$non_compliant_uids" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found non-compliant duplicate User IDs (UIDs):")
        
        for uid in $non_compliant_uids; do
            users=$(awk -F: -v u="$uid" '$3==u {print $1}' "$PASSWD_FILE" | xargs)
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - UID '$uid' is used by: $users")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No non-compliant duplicate UIDs were found.")
        if echo "$duplicate_uids" | grep -q "0"; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Note: UID 0 is correctly shared by 'root' and 'toor' on FreeBSD.")
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

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Review the accounts with duplicate UIDs."
        printf '%s\n' "Assign a unique UID to each user account as required."
    fi
}
BASH
    ],

    [ 'id' => '6.2.5', 'title' => 'Ensure no duplicate GIDs exist', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    GROUP_FILE="/etc/group"

    duplicate_gids=$(cut -f3 -d: "$GROUP_FILE" | sort -n | uniq -d)

    if [ -n "$duplicate_gids" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Duplicate Group IDs (GIDs) were found:")
        
        echo "$duplicate_gids" | while IFS= read -r gid; do
            groups=$(awk -F: -v g="$gid" '$3==g {print $1}' "$GROUP_FILE" | xargs)
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - GID '$gid' is used by: $groups")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No duplicate GIDs were found in '$GROUP_FILE'.")
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
        printf '%s\n' "Review the groups with duplicate GIDs."
        printf '%s\n' "Assign a unique GID to each group name as required."
    fi
}
BASH
    ],

    [ 'id' => '6.2.6', 'title' => 'Ensure no duplicate user names exist', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PASSWD_FILE="/etc/passwd"

    duplicate_users=$(cut -f1 -d: "$PASSWD_FILE" | sort | uniq -d)

    if [ -n "$duplicate_users" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Duplicate usernames were found:")
        
        echo "$duplicate_users" | while IFS= read -r user_name; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - Username '$user_name' is duplicated")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No duplicate usernames were found in '$PASSWD_FILE'.")
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
        printf '%s\n' "Review the accounts with duplicate names in '$PASSWD_FILE' and ensure each user has a unique name."
    fi
}
BASH
    ],

    [ 'id' => '6.2.7', 'title' => 'Ensure no duplicate group names exist', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    GROUP_FILE="/etc/group"

    duplicate_groups=$(cut -f1 -d: "$GROUP_FILE" | sort | uniq -d)

    if [ -n "$duplicate_groups" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Duplicate group names were found:")
        
        echo "$duplicate_groups" | while IFS= read -r group_name; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - Group name '$group_name' is duplicated")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No duplicate group names were found in '$GROUP_FILE'.")
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
        printf '%s\n' "Review the groups with duplicate names in '$GROUP_FILE' and ensure each group has a unique name."
    fi
}
BASH
    ],

    [ 'id' => '6.2.8', 'title' => 'Ensure root path integrity', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if [ "$(id -u)" -ne 0 ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - This check must be run as the root user to audit the correct PATH."
        exit 0
    fi

    case "$PATH" in
        *:)
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - PATH variable has a trailing colon.")
            ;;
        *)
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - PATH variable does not have a trailing colon.")
            ;;
    esac

    OLD_IFS="$IFS"
    IFS=:
    for dir in $PATH; do
        IFS="$OLD_IFS"

        if [ -z "$dir" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - PATH contains an empty directory (::).")
            continue # Go to the next directory in the loop
        fi
        
        if [ "$dir" = "." ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - PATH contains the current working directory (.).")
        fi

        if [ ! -d "$dir" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Location '$dir' in PATH is not a directory.")
            continue
        fi

        owner=$(stat -f '%Su' "$dir")
        perms=$(stat -f '%Lp' "$dir")

        if [ "$owner" != "root" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Directory '$dir' in PATH is not owned by root (owned by '$owner').")
        fi
        
        perms_masked=$(($perms & 002))
        if [ "$perms_masked" -ne 0 ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Directory '$dir' in PATH has permissions ('$perms') more permissive than 755.")
        fi
    done
    IFS="$OLD_IFS"

    if [ -z "$OUTPUT_FAIL" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All directories in root's PATH are secure.")
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
        printf '%s\n' "Review the root user's PATH variable in shell profile files (e.g., /root/.profile)."
        printf '%s\n' "Ensure it does not contain empty paths, '.', or any non-root-owned or world-writable directories."
    fi
}
BASH
    ],

    [ 'id' => '6.2.9', 'title' => 'Ensure root is the only UID 0 account', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PASSWD_FILE="/etc/passwd"
    
    uid_0_accounts=$(awk -F: '($3 == 0) { print $1 }' "$PASSWD_FILE")

    non_compliant_accounts=""
    for user in $uid_0_accounts; do
        case "$user" in
            root|toor)
                ;;
            *)
                non_compliant_accounts="$non_compliant_accounts $user"
                ;;
        esac
    done

    if [ -n "$non_compliant_accounts" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following non-root account(s) have UID 0:$non_compliant_accounts")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Only 'root' and/or 'toor' accounts have UID 0.")
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
        printf '%s\n' "Review the accounts listed above."
        printf '%s\n' "Assign a new, unique UID to any non-root account that has a UID of 0."
    fi
}
BASH
    ],

    [ 'id' => '6.2.10', 'title' => 'Ensure local interactive user home directories are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    local_users=$(awk -F: '($3 >= 1000) && ($7 !~ /(\/sbin\/nologin|\/usr\/bin\/false)/) {print $1":"$6":"$4}' /etc/passwd)

    if [ -z "$local_users" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - No local interactive users found to check.")
    else
        echo "$local_users" | while IFS=: read -r username homedir gid; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking user '$username' with home directory '$homedir'")
            
            if [ ! -d "$homedir" ]; then
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Home directory '$homedir' does not exist.")
                continue # Skip other checks for this user
            fi

            owner=$(stat -f '%Su' "$homedir")
            if [ "$owner" = "$username" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - User '$username': Home directory is correctly owned by user.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Home directory is owned by '$owner', not '$username'.")
            fi

            group_name=$(getent group "$gid" | cut -d: -f1)
            dir_group=$(stat -f '%Sg' "$homedir")
            if [ "$dir_group" = "$group_name" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - User '$username': Home directory group is correct ('$dir_group').")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Home directory group is '$dir_group', but should be '$group_name'.")
            fi

            perms=$(stat -f '%Lp' "$homedir")
            perms_masked=$(($perms & 027))
            if [ "$perms_masked" -eq 0 ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - User '$username': Home directory permissions ('$perms') are compliant.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Home directory permissions ('$perms') are not compliant (should be 750 or more restrictive).")
            fi
        done
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - All local interactive user home directories are correctly configured."
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "For each failing user, ensure their home directory exists, is owned by them,"
        printf '%s\n' "and has permissions of 750 or more restrictive."
        printf '%s\n' "Example for user 'jdoe':"
        printf '%s\n' "# mkdir -p /home/jdoe"
        printf '%s\n' "# chown jdoe:jdoe /home/jdoe"
        printf '%s\n' "# chmod 750 /home/jdoe"
    fi
}
BASH
    ],

    [ 'id' => '6.2.11', 'title' => 'Ensure local interactive user dot files access is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    # Format: username:homedir:gid
    local_users=$(awk -F: '($3 >= 1000) && ($7 !~ /(\/sbin\/nologin|\/usr\/bin\/false)/) {print $1":"$6":"$4}' /etc/passwd)

    if [ -z "$local_users" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - No local interactive users found to check.")
    else
        # --- Iterate over each user ---
        echo "$local_users" | while IFS=: read -r username homedir gid; do
            if [ ! -d "$homedir" ]; then
                OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Home directory for '$username' not found, skipping.")
                continue
            fi

            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking dot files for user '$username' in '$homedir'")

            for file in "$homedir"/.*; do
                if [ ! -f "$file" ] || [ ! -r "$file" ]; then continue; fi

                filename=$(basename "$file")
                perms=$(stat -f '%Lp' "$file")
                owner=$(stat -f '%Su' "$file")
                group_id=$(stat -f '%g' "$file")

                case "$filename" in
                    .forward|.rhosts)
                        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Prohibited file found: $filename")
                        continue # Move to next file
                        ;;
                    .netrc)
                        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': Prohibited file found: $filename. If required, its permissions must be 600.")
                        ;;
                esac

                if [ "$owner" != "$username" ]; then
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': File '$filename' is not owned by user (owned by '$owner').")
                fi
                if [ "$group_id" -ne "$gid" ]; then
                     OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': File '$filename' is not owned by primary group (GID '$group_id' vs '$gid').")
                fi

                case "$filename" in
                    .sh_history|.history|.netrc)
                        perms_masked=$(($perms & 077)) # Must be 600 or more restrictive
                        if [ "$perms_masked" -ne 0 ]; then
                            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': File '$filename' permissions ('$perms') are not 600 or more restrictive.")
                        fi
                        ;;
                    *)
                        perms_masked=$(($perms & 022)) # Must be 644 or more restrictive
                        if [ "$perms_masked" -ne 0 ]; then
                            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - User '$username': File '$filename' permissions ('$perms') are not 644 or more restrictive.")
                        fi
                        ;;
                esac
            done
        done
    fi

    if [ -z "$OUTPUT_FAIL" ] && [ -n "$OUTPUT_INFO" ]; then
        OUTPUT_PASS="  - All checked user dot files are configured correctly."
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** MANUAL **"
        printf '%s\n' "$OUTPUT_PASS"
        printf '%s\n' "  - Please manually confirm these settings align with your site policy."
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Review the failing items. Remove prohibited files (e.g., .forward, .rhosts)."
        printf '%s\n' "Correct ownership with 'chown' and permissions with 'chmod' as needed."
    fi
}
BASH
    ],
];
