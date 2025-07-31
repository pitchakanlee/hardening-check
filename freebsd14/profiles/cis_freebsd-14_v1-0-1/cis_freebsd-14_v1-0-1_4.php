<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.4.pdf
// =============================================================
return [
    [ 'id' => '4', 'title' => 'Access, Authentication and Authorization', 'type' => 'header'],

    [ 'id' => '4.1', 'title' => 'Configure job schedulers', 'type' => 'header'],

    [ 'id' => '4.1.1', 'title' => 'Configure cron', 'type' => 'header'],

    [ 'id' => '4.1.1.1', 'title' => 'Ensure permissions on /etc/crontab are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/crontab"

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        perms_masked=$(($perms & 033))
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/crontab:"
        printf '%s\n' "# chown root:wheel /etc/crontab"
        printf '%s\n' "# chmod og-rwx /etc/crontab"
    fi
}
BASH
    ],

    [ 'id' => '4.1.1.2', 'title' => 'Ensure permissions on /etc/cron.d are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_DIR="/etc/cron.d"

    if [ ! -d "$TARGET_DIR" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Directory '$TARGET_DIR' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_DIR")
        
        perms_symbolic=$(stat -f '%Sp' "$TARGET_DIR")
        owner=$(stat -f '%Su' "$TARGET_DIR")
        group=$(stat -f '%Sg' "$TARGET_DIR")

        if [ "$(echo "$perms_symbolic" | cut -c 5-10)" = "------" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Permissions '$perms_symbolic' are compliant (no group/other permissions).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Permissions are '$perms_symbolic', but group or other have permissions.")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/cron.d:"
        printf '%s\n' "# chown root:wheel /etc/cron.d"
        printf '%s\n' "# chmod og-rwx /etc/cron.d"
    fi
}
BASH
    ],

    [ 'id' => '4.1.1.3', 'title' => 'Ensure crontab is restricted to authorized users', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if ! pkg query -g %n 'cron' >/dev/null 2>&1; then
        printf '\n%s\n' "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - Package 'cron' is not installed, this check is not applicable."
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package 'cron' is installed, proceeding with access control file checks.")
        
        ALLOW_FILE="/var/cron/allow"
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing file: $ALLOW_FILE")

        if [ ! -f "$ALLOW_FILE" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$ALLOW_FILE' does not exist, which is required.")
        else
            perms=$(stat -f '%p' "$ALLOW_FILE")
            owner=$(stat -f '%Su' "$ALLOW_FILE")
            group=$(stat -f '%Sg' "$ALLOW_FILE")

            perms_masked=$(($perms & 0137))
            if [ "$perms_masked" -eq 0 ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $ALLOW_FILE: Permissions '$perms' are compliant (640 or less).")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $ALLOW_FILE: Permissions are '$perms', but should be 640 or more restrictive.")
            fi
            if [ "$owner" = "root" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $ALLOW_FILE: Owner is 'root'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $ALLOW_FILE: Owner is '$owner', but should be 'root'.")
            fi
            if [ "$group" = "wheel" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $ALLOW_FILE: Group is 'wheel'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $ALLOW_FILE: Group is '$group', but should be 'wheel'.")
            fi
        fi

        DENY_FILE="/var/cron/deny"
        if [ -f "$DENY_FILE" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing file: $DENY_FILE")
            
            perms=$(stat -f '%p' "$DENY_FILE")
            owner=$(stat -f '%Su' "$DENY_FILE")
            group=$(stat -f '%Sg' "$DENY_FILE")
            
            perms_masked=$(($perms & 0137))
            if [ ! "$perms_masked" -eq 0 ]; then
                 OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $DENY_FILE: Permissions are '$perms', but should be 640 or more restrictive.")
            fi
            if [ ! "$owner" = "root" ]; then
                 OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $DENY_FILE: Owner is '$owner', but should be 'root'.")
            fi
            if [ ! "$group" = "wheel" ]; then
                 OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $DENY_FILE: Group is '$group', but should be 'wheel'.")
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
            printf '%s\n' "Run the following commands to correctly configure cron access control:"
            printf '%s\n' "# touch /var/cron/allow"
            printf '%s\n' "# chown root:wheel /var/cron/allow"
            printf '%s\n' "# chmod u-x,g-wx,o-rwx /var/cron/allow"
        fi
    fi
}
BASH
    ],    

    [ 'id' => '4.1.2', 'title' => 'Configure at', 'type' => 'header'],

    [ 'id' => '4.1.2.1', 'title' => 'Ensure at is restricted to authorized users', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    check_file_perms() {
        TARGET_FILE=$1
        IS_REQUIRED=$2

        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing file: $TARGET_FILE")

        if [ ! -f "$TARGET_FILE" ]; then
            if [ "$IS_REQUIRED" = "yes" ]; then
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' does not exist, which is required.")
            else
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - File '$TARGET_FILE' does not exist, which is a compliant state.")
            fi
            return
        fi

        perms=$(stat -f '%Lp' "$TARGET_FILE")
        owner=$(stat -f '%Su' "$TARGET_FILE")
        group=$(stat -f '%Sg' "$TARGET_FILE")

        perms_masked=$(($perms & 037))
        if [ "$perms_masked" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $TARGET_FILE: Permissions '$perms' are compliant (640 or less).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $TARGET_FILE: Permissions are '$perms', but should be 640 or more restrictive.")
        fi

        if [ "$owner" = "root" ] || [ "$owner" = "daemon" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $TARGET_FILE: Owner is '$owner', which is compliant.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $TARGET_FILE: Owner is '$owner', but should be 'root' or 'daemon'.")
        fi

        if [ "$group" = "wheel" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $TARGET_FILE: Group is 'wheel'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $TARGET_FILE: Group is '$group', but should be 'wheel'.")
        fi
    }

    check_file_perms "/var/at/at.allow" "yes"
    check_file_perms "/var/at/at.deny" "no"

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

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to correctly configure 'at' access control:"
        printf '%s\n' "# touch /var/at/at.allow"
        printf '%s\n' "# chown root:wheel /var/at/at.allow"
        printf '%s\n' "# chmod u-x,g-wx,o-rwx /var/at/at.allow"
        printf '%s\n' "# [ -f /var/at/at.deny ] && rm /var/at/at.deny"
    fi
}
BASH
    ],

    [ 'id' => '4.2', 'title' => 'Configure SSH Server', 'type' => 'header'],

    [ 'id' => '4.2.1', 'title' => 'Ensure permissions on /etc/ssh/sshd_config are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    TARGET_FILE="/etc/ssh/sshd_config"

    if [ ! -f "$TARGET_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - File '$TARGET_FILE' not found.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking permissions for: $TARGET_FILE")
        
        perms=$(stat -f '%p' "$TARGET_FILE")
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
        printf '%s\n' "Run the following commands to set correct permissions on /etc/ssh/sshd_config:"
        printf '%s\n' "# chown root:wheel /etc/ssh/sshd_config"
        printf '%s\n' "# chmod u-x,og-rwx /etc/ssh/sshd_config"
    fi
}
BASH
    ],

    [ 'id' => '4.2.2', 'title' => 'Ensure permissions on SSH private host key files are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    
    key_files=$(find /etc/ssh -type f -name "ssh_host_*_key")

    if [ -z "$key_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No SSH private host key files were found in /etc/ssh.")
    else
        for file_path in $key_files; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking file: $file_path")
            
            perms_symbolic=$(stat -f '%Sp' "$file_path") # e.g., "-rw-------"
            owner=$(stat -f '%Su' "$file_path")
            group=$(stat -f '%Sg' "$file_path")
            
            if [ "$(echo "$perms_symbolic" | cut -c 5-10)" = "------" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Permissions '$perms_symbolic' are compliant.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Permissions are '$perms_symbolic', but should be 600 or more restrictive.")
            fi

            if [ "$owner" = "root" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Owner is 'root'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Owner is '$owner', but should be 'root'.")
            fi

            if [ "$group" = "wheel" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Group is 'wheel'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Group is '$group', but should be 'wheel'.")
            fi
        done
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
        printf '%s\n' "Run the following command to set correct permissions on all private host keys:"
        printf '%s\n' "# find /etc/ssh -type f -name \"ssh_host_*_key\" -exec chmod 600 {} \\; -exec chown root:wheel {} \\;"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.3', 'title' => 'Ensure permissions on SSH public host key files are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    
    key_files=$(find /etc/ssh -type f -name "ssh_host_*_key.pub")

    if [ -z "$key_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No SSH public host key files were found in /etc/ssh.")
    else
        for file_path in $key_files; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking file: $file_path")
            
            perms=$(stat -f '%Lp' "$file_path")
            owner=$(stat -f '%Su' "$file_path")
            group=$(stat -f '%Sg' "$file_path")
            
            perms_masked=$(($perms & 022))
            if [ "$perms_masked" -eq 0 ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Permissions '$perms' are compliant.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Permissions are '$perms', but should be 644 or more restrictive.")
            fi

            if [ "$owner" = "root" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Owner is 'root'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Owner is '$owner', but should be 'root'.")
            fi

            if [ "$group" = "wheel" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Group is 'wheel'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Group is '$group', but should be 'wheel'.")
            fi
        done
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
        printf '%s\n' "Run the following command to set correct permissions on all public host keys:"
        printf '%s\n' "# find /etc/ssh -type f -name \"ssh_host_*_key.pub\" -exec chmod 644 {} \\; -exec chown root:wheel {} \\;"
    fi
}
BASH
    ],

    [ 'id' => '4.2.4', 'title' => 'Ensure sshd access is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"

    live_config=$(sshd -T | grep -Ei '^\s*(allow|deny)(users|groups)\s+\S+')
    
    if [ -n "$live_config" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration contains access control rules.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live Config Rules Found:")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$live_config")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No access control rules found in the live SSH configuration.")
    fi

    static_config=$(grep -Ei '^\s*(#)?\s*(allow|deny)(users|groups)\s+\S+' "$CONFIG_FILE")
    
    if [ -n "$static_config" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Static Config File (/etc/ssh/sshd_config) Contains:")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$static_config")
    fi
    

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** REVIEW **"
        printf '%s\n' "$OUTPUT_PASS"
        printf '%s\n' "  - Please manually review the users/groups above against your site policy."
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        if [ -n "$OUTPUT_PASS" ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "$OUTPUT_PASS"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit /etc/ssh/sshd_config to include at least one of the following,"
        printf '%s\n' "according to your site policy:"
        printf '%s\n' "AllowUsers <userlist>"
        printf '%s\n' "AllowGroups <grouplist>"
        printf '%s\n' "DenyUsers <userlist>"
        printf '%s\n' "DenyGroups <grouplist>"
    fi
}
BASH
    ],

    [ 'id' => '4.2.5', 'title' => 'Ensure sshd Banner is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    host_name=$(hostname)
    # Get the primary IP address associated with the hostname from /etc/hosts
    host_addr=$(grep -w "$host_name" /etc/hosts | awk '{print $1}' | head -n 1)

    if [ -z "$host_name" ] || [ -z "$host_addr" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine local hostname or IP address from /etc/hosts to run test.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Testing live config with: user=root, host=$host_name, addr=$host_addr")
        
        banner_config=$(sshd -T -C user=root -C host="$host_name" -C addr="$host_addr" | grep -i '^\s*banner\s')

        if [ -n "$banner_config" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'Banner' directive is configured in the live SSH configuration.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live config reports: $banner_config")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'Banner' directive is not configured in the live SSH configuration.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and add the 'Banner' directive."
        printf '%s\n' "Example:"
        printf '%s\n' "Banner /etc/issue.net"
    fi
}
BASH
    ],

    [ 'id' => '4.2.6', 'title' => 'Ensure sshd Ciphers are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    live_config=$(sshd -T | grep -i '^\s*ciphers\s' | awk '{print $2}')

    if [ -z "$live_config" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the list of active ciphers.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Actively configured Ciphers: $live_config")
        
        weak_ciphers="3des-cbc aes128-cbc aes192-cbc aes256-cbc rijndael-cbc@lysator.liu.se"
        found_weak=0

        for cipher in $weak_ciphers; do
            # Use 'case' for a portable substring check in sh
            case "$live_config" in
                *$cipher*)
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Weak cipher '$cipher' is enabled.")
                    found_weak=1
                    ;;
            esac
        done

        if [ "$found_weak" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No weak ciphers were found in the active configuration.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'Ciphers' line."
        printf '%s\n' "To disable weak ciphers, prefix them with a minus sign (-)."
        printf '%s\n' "Example:"
        printf '%s\n' "Ciphers -3des-cbc,aes128-cbc,aes192-cbc,aes256-cbc,rijndael-cbc@lysator.liu.se"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.7', 'title' => 'Ensure sshd ClientAliveInterval and ClientAliveCountMax are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    live_config=$(sshd -T | grep -E '^\s*(clientaliveinterval|clientalivecountmax)\s')

    interval_line=$(echo "$live_config" | grep clientaliveinterval)
    interval_value=$(echo "$interval_line" | awk '{print $2}')

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current ClientAliveInterval: $interval_value")
    if [ "$interval_value" -gt 0 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'ClientAliveInterval' is configured to a non-zero value ('$interval_value').")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'ClientAliveInterval' is set to '0', which disables the check.")
    fi

    count_line=$(echo "$live_config" | grep clientalivecountmax)
    count_value=$(echo "$count_line" | awk '{print $2}')

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current ClientAliveCountMax: $count_value")
    if [ "$count_value" -gt 0 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'ClientAliveCountMax' is configured to a non-zero value ('$count_value').")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'ClientAliveCountMax' is set to '0', which disables the check.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config to set ClientAliveInterval and ClientAliveCountMax."
        printf '%s\n' "Example:"
        printf '%s\n' "ClientAliveInterval 15"
        printf '%s\n' "ClientAliveCountMax 3"
    fi
}
BASH
    ],

    [ 'id' => '4.2.8', 'title' => 'Ensure sshd DisableForwarding is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="disableforwarding"
    EXPECTED_VALUE="yes"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live config value for '$PARAM_NAME' is: $live_value")

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i '^\s*DisableForwarding\s\+no' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'DisableForwarding no' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'DisableForwarding no' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "DisableForwarding yes"
    fi
}
BASH
    ],

    [ 'id' => '4.2.9', 'title' => 'Ensure sshd HostbasedAuthentication is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="hostbasedauthentication"
    EXPECTED_VALUE="no"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live config value for '$PARAM_NAME' is: $live_value")

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i '^\s*HostbasedAuthentication\s\+yes' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'HostbasedAuthentication yes' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'HostbasedAuthentication yes' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "HostbasedAuthentication no"
    fi
}
BASH
    ],

    [ 'id' => '4.2.10', 'title' => 'Ensure sshd IgnoreRhosts is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="ignorerhosts"
    EXPECTED_VALUE="yes"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live config value for '$PARAM_NAME' is: $live_value")

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i '^\s*IgnoreRhosts\s\+no' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'IgnoreRhosts no' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'IgnoreRhosts no' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "IgnoreRhosts yes"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.11', 'title' => 'Ensure sshd KexAlgorithms is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    live_config=$(sshd -T | grep -i '^\s*kexalgorithms\s' | awk '{print $2}')

    if [ -z "$live_config" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the list of active Key Exchange Algorithms.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Actively configured KexAlgorithms: $live_config")
        
        weak_algos="diffie-hellman-group1-sha1 diffie-hellman-group14-sha1 diffie-hellman-group-exchange-sha1"
        found_weak=0

        for algo in $weak_algos; do
            # Use 'case' for a portable substring check in sh
            case "$live_config" in
                *$algo*)
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Weak Key Exchange algorithm '$algo' is enabled.")
                    found_weak=1
                    ;;
            esac
        done

        if [ "$found_weak" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No weak Key Exchange algorithms were found in the active configuration.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'KexAlgorithms' line."
        printf '%s\n' "To disable weak algorithms, prefix them with a minus sign (-)."
        printf '%s\n' "Example:"
        printf '%s\n' "KexAlgorithms -diffie-hellman-group1-sha1,diffie-hellman-group14-sha1"
    fi
}
BASH
    ],

    [ 'id' => '4.2.12', 'title' => 'Ensure sshd LoginGraceTime is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="logingracetime"

    config_line=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s")
    config_value_raw=$(echo "$config_line" | awk '{print $2}')

    if [ -z "$config_value_raw" ]; then
        config_value_raw="120"
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: 120s")
    fi
    
    grace_time_seconds=$config_value_raw
    case "$grace_time_seconds" in
        *m)
            minutes=$(echo "$grace_time_seconds" | sed 's/m//')
            grace_time_seconds=$(($minutes * 60))
            ;;
    esac

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current $PARAM_NAME is '$config_value_raw' (evaluates to $grace_time_seconds seconds).")

    if [ "$grace_time_seconds" -ge 1 ] && [ "$grace_time_seconds" -le 60 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$PARAM_NAME' is correctly configured to be between 1 and 60 seconds.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is '$grace_time_seconds' seconds, which is not within the compliant range of 1-60.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config to set the LoginGraceTime parameter."
        printf '%s\n' "The value should be between 1 and 60 seconds."
        printf '%s\n' "Example:"
        printf '%s\n' "LoginGraceTime 60"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.13', 'title' => 'Ensure sshd LogLevel is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="loglevel"

    live_value=$(sudo sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the live configuration for '$PARAM_NAME'.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    
        # --- Check if the value is compliant ---
        if [ "$live_value" = "VERBOSE" ] || [ "$live_value" = "INFO" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$PARAM_NAME' is set to a compliant value ('$live_value').")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is set to '$live_value', but should be 'VERBOSE' or 'INFO'.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the LogLevel parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "LogLevel VERBOSE"
    fi
}
BASH
    ],

    [ 'id' => '4.2.14', 'title' => 'Ensure sshd MACs are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    live_config=$(sudo sshd -T | grep -i '^\s*macs\s' | awk '{print $2}')

    if [ -z "$live_config" ]; then
        # This case is unlikely as sshd -T usually provides a default.
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the list of active MACs.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Actively configured MACs: $live_config")
        
        weak_macs="hmac-md5 hmac-md5-96 hmac-ripemd160 hmac-sha1-96 umac-64@openssh.com hmac-md5-etm@openssh.com hmac-md5-96-etm@openssh.com hmac-ripemd160-etm@openssh.com hmac-sha1-96-etm@openssh.com umac-64-etm@openssh.com"
        found_weak=0

        for mac in $weak_macs; do
            # Use 'case' for a portable substring check in sh
            case "$live_config" in
                *$mac*)
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Weak MAC '$mac' is enabled.")
                    found_weak=1
                    ;;
            esac
        done

        if [ "$found_weak" -eq 0 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No weak MACs were found in the active configuration.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and add or modify the 'MACs' line."
        printf '%s\n' "To disable weak MACs, you can prefix them with a minus sign (-)."
        printf '%s\n' "Example:"
        printf '%s\n' "MACs -hmac-md5,hmac-md5-96,hmac-ripemd160,hmac-sha1-96,umac-64@openssh.com"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.15', 'title' => 'Ensure sshd MaxAuthTries is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="maxauthtries"
    MAX_VALUE=4

    live_value=$(sudo sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        live_value=6 
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: $live_value")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    fi

    if [ "$live_value" -le "$MAX_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$PARAM_NAME' is set to '$live_value', which is compliant (<= $MAX_VALUE).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is set to '$live_value', but should be $MAX_VALUE or less.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the MaxAuthTries parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxAuthTries 4"
    fi
}
BASH
    ],

    [ 'id' => '4.2.16', 'title' => 'Ensure sshd MaxSessions is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="maxsessions"
    MAX_VALUE=10

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        live_value=10 # Assume default if not explicitly set
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: $live_value")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    fi

    if [ "$live_value" -le "$MAX_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$PARAM_NAME' is set to '$live_value', which is compliant (<= $MAX_VALUE).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is set to '$live_value', but should be $MAX_VALUE or less.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the MaxSessions parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxSessions 10"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.17', 'title' => 'Ensure sshd MaxStartups is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PARAM_NAME="maxstartups"
    
    config_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')
    
    if [ -z "$config_value" ]; then
        config_value="10:30:100" # Explicitly use default if not set
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: $config_value")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $config_value")
    fi

    start=$(echo "$config_value" | cut -d: -f1)
    rate=$(echo "$config_value" | cut -d: -f2)
    full=$(echo "$config_value" | cut -d: -f3)

    if [ "$start" -le 10 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'start' value ('$start') is compliant (<= 10).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'start' value ('$start') is not compliant (> 10).")
    fi

    if [ "$rate" -le 30 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'rate' value ('$rate') is compliant (<= 30).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'rate' value ('$rate') is not compliant (> 30).")
    fi

    if [ "$full" -le 60 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'full' value ('$full') is compliant (<= 60).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'full' value ('$full') is not compliant (> 60).")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config to set the MaxStartups parameter."
        printf '%s\n' "Example:"
        printf '%s\n' "MaxStartups 10:30:60"
    fi
}
BASH
    ],

    [ 'id' => '4.2.18', 'title' => 'Ensure sshd PermitEmptyPasswords is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="permitemptypasswords"
    EXPECTED_VALUE="no"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        live_value="no" # Assume default if not explicitly set
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: $live_value")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    fi

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i '^\s*PermitEmptyPasswords\s\+yes' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'PermitEmptyPasswords yes' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'PermitEmptyPasswords yes' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "PermitEmptyPasswords no"
    fi
}
BASH
    ],    

    [ 'id' => '4.2.19', 'title' => 'Ensure sshd PermitRootLogin is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="permitrootlogin"
    EXPECTED_VALUE="no"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Live config value for '$PARAM_NAME' is: $live_value")

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i -E '^\s*PermitRootLogin\s+(yes|prohibit-password|forced-commands-only)' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a non-compliant setting for PermitRootLogin.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a non-compliant override for PermitRootLogin.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "PermitRootLogin no"
    fi
}
BASH
    ],

    [ 'id' => '4.2.20', 'title' => 'Ensure sshd PermitUserEnvironment is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="permituserenvironment"
    EXPECTED_VALUE="no"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        live_value="no" # Assume default if not explicitly set
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - '$PARAM_NAME' is not explicitly set, using default value: $live_value")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    fi

    if [ "$live_value" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
    fi

    if grep -q -i '^\s*PermitUserEnvironment\s\+yes' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'PermitUserEnvironment yes' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'PermitUserEnvironment yes' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "PermitUserEnvironment no"
    fi
}
BASH
    ],

        [ 'id' => '4.2.21', 'title' => 'Ensure sshd UsePAM is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/ssh/sshd_config"
    PARAM_NAME="usepam"
    EXPECTED_VALUE="yes"

    live_value=$(sshd -T | grep -i "^\s*${PARAM_NAME}\s" | awk '{print $2}')

    if [ -z "$live_value" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the live configuration for '$PARAM_NAME'.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current '$PARAM_NAME' setting is: $live_value")
    
        if [ "$live_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Live SSH configuration for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Live SSH configuration for '$PARAM_NAME' is '$live_value', but should be '$EXPECTED_VALUE'.")
        fi
    fi

    if grep -q -i '^\s*UsePAM\s\+no' "$CONFIG_FILE"; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Static file '$CONFIG_FILE' contains a 'UsePAM no' setting, which is non-compliant.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Static file '$CONFIG_FILE' does not contain a 'UsePAM no' setting.")
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
        printf '%s\n' "Edit /etc/ssh/sshd_config and set the following parameter:"
        printf '%s\n' "UsePAM yes"
    fi
}
BASH
    ],

    [ 'id' => '4.3', 'title' => 'Configure privilege escalation', 'type' => 'header'],

    [ 'id' => '4.3.1', 'title' => 'Ensure sudo is installed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PACKAGE_NAME="sudo"

    if pkg query -g %n "$PACKAGE_NAME" >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package '$PACKAGE_NAME' is installed.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Package '$PACKAGE_NAME' is not installed.")
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
        printf '%s\n' "Run the following command to install sudo:"
        printf '%s\n' "# pkg install -y $PACKAGE_NAME"
    fi
}
BASH
    ],

    [ 'id' => '4.3.2', 'title' => 'Ensure sudo commands use pty', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SUDOERS_FILES="/usr/local/etc/sudoers /usr/local/etc/sudoers.d/*"

    if grep -rE '^\s*Defaults\s+([^#\n\r]+,)?use_pty\b' $SUDOERS_FILES >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'Defaults use_pty' is correctly set in a sudoers file.")
        found_config=$(grep -rE '^\s*Defaults\s+([^#\n\r]+,)?use_pty\b' $SUDOERS_FILES)
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting in: $found_config")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'Defaults use_pty' is not set in any sudoers file.")
    fi

    if grep -rE '^\s*Defaults\s+([^#\n\r]+,)?!use_pty\b' $SUDOERS_FILES >/dev/null 2>&1; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found a 'Defaults !use_pty' setting, which disables the requirement.")
        found_override=$(grep -rE '^\s*Defaults\s+([^#\n\r]+,)?!use_pty\b' $SUDOERS_FILES)
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found override in: $found_override")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No 'Defaults !use_pty' override was found.")
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
        printf '%s\n' "Edit /usr/local/etc/sudoers (using 'visudo') or a file in /usr/local/etc/sudoers.d/"
        printf '%s\n' "and add the following line:"
        printf '%s\n' "Defaults use_pty"
        printf '%s\n' "Also, ensure any line containing '!use_pty' is removed."
    fi
}
BASH
    ],    

    [ 'id' => '4.3.3', 'title' => 'Ensure sudo log file exists', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SUDOERS_FILES="/usr/local/etc/sudoers /usr/local/etc/sudoers.d/*"

    logfile_config=$(grep -rE '^\s*Defaults\s+([^#\n\r]+,)?logfile\s*=' $SUDOERS_FILES 2>/dev/null)

    if [ -n "$logfile_config" ]; then
        log_path=$(echo "$logfile_config" | sed -n 's/.*logfile[[:space:]]*=[[:space:]]*//p' | tr -d "\'\"")

        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'Defaults logfile' is configured.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $logfile_config")

        if [ -f "$log_path" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The configured log file '$log_path' exists.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured log file '$log_path' does NOT exist.")
        fi
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'Defaults logfile' is not set in any sudoers file.")
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
        printf '%s\n' "Edit /usr/local/etc/sudoers (using 'visudo') or a file in /usr/local/etc/sudoers.d/ and add the following line:"
        printf '%s\n' "Defaults logfile=\"/var/log/sudo.log\""
    fi
}
BASH
    ],

    [ 'id' => '4.3.4', 'title' => 'Ensure users must provide password for escalation', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SUDOERS_FILES="/usr/local/etc/sudoers /usr/local/etc/sudoers.d/*"

    nopasswd_entries=$(grep -rE '^\s*[^#].*\sNOPASSWD:' $SUDOERS_FILES 2>/dev/null)

    if [ -n "$nopasswd_entries" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - One or more sudoers entries contain a 'NOPASSWD' tag.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following non-compliant entries:")
        
        echo "$nopasswd_entries" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No active 'NOPASSWD' tags were found in any sudoers file.")
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
        printf '%s\n' "Edit the relevant sudoers file(s) using 'visudo' and remove any"
        printf '%s\n' "occurrences of the 'NOPASSWD' tag."
    fi
}
BASH
    ],

    [ 'id' => '4.3.5', 'title' => 'Ensure re-authentication for privilege escalation is not disabled globally', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SUDOERS_FILES="/usr/local/etc/sudoers /usr/local/etc/sudoers.d/*"

    authenticate_override=$(grep -rE '^\s*[^#].*!authenticate' $SUDOERS_FILES 2>/dev/null)

    if [ -n "$authenticate_override" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Found a '!authenticate' tag, which globally disables re-authentication.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following non-compliant entries:")
        
        echo "$authenticate_override" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No '!authenticate' tags were found in any sudoers file.")
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
        printf '%s\n' "Edit the relevant sudoers file(s) using 'visudo' and remove any"
        printf '%s\n' "occurrences of the '!authenticate' tag."
    fi
}
BASH
    ],

    [ 'id' => '4.3.6', 'title' => 'Ensure sudo authentication timeout is configured correctly', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SUDOERS_FILES="/usr/local/etc/sudoers /usr/local/etc/sudoers.d/*"

    configured_timeout=$(grep -rE '^\s*Defaults\s+.*timestamp_timeout\s*=' $SUDOERS_FILES 2>/dev/null | sed -n 's/.*timestamp_timeout[[:space:]]*=[[:space:]]*//p' | tail -n 1)

    if [ -n "$configured_timeout" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found 'timestamp_timeout' explicitly set to: $configured_timeout minutes.")
        
        case "$configured_timeout" in
            -[1-9]*) # Handles -1 and other negative numbers
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured timeout of '$configured_timeout' minutes disables the timeout, which is not compliant.")
                ;;
            [0-9] | 1[0-5]) # Handles 0 through 15
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The configured timeout of '$configured_timeout' minutes is compliant (<= 15).")
                ;;
            *) # Handles numbers greater than 15
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured timeout of '$configured_timeout' minutes is not compliant (should be 15 or less).")
                ;;
        esac
    else
        default_timeout=$(sudo -V | grep "Authentication timestamp timeout:" | awk '{print $4}')
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - 'timestamp_timeout' is not explicitly set. Using the compiled default.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Sudo default timeout is: $default_timeout minutes.")

        if [ "$default_timeout" -le 15 ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The default timeout of '$default_timeout' minutes is compliant (<= 15).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The default timeout of '$default_timeout' minutes is not compliant (should be 15 or less).")
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
        printf '%s\n' "Edit /usr/local/etc/sudoers (using 'visudo') and add or modify the 'timestamp_timeout' setting."
        printf '%s\n' "The value should be 15 or less. A value of 0 will always prompt for a password."
        printf '%s\n' "Example:"
        printf '%s\n' "Defaults    timestamp_timeout=15"
    fi
}
BASH
    ],    

    [ 'id' => '4.3.7', 'title' => 'Ensure access to the su command is restricted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PAM_SU_FILE="/etc/pam.d/su"

    auth_rule=$(grep -E '^\s*auth\s+(required|requisite)\s+pam_group\.so' "$PAM_SU_FILE")

    if [ -z "$auth_rule" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No rule found in '$PAM_SU_FILE' to restrict su access using pam_group.so.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - A rule using pam_group.so was found in '$PAM_SU_FILE'.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found Rule: $auth_rule")
        
        group_name=$(echo "$auth_rule" | sed -n 's/.*group=\([^[:space:]]*\).*/\1/p')

        if [ -z "$group_name" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not determine the restricted group name from the pam_group.so rule.")
        else
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - The restricted group is '$group_name'.")
            
            user_list=$(grep "^${group_name}:" /etc/group | cut -d: -f4)

            if [ -z "$user_list" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The group '$group_name' has no members, which is compliant.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The group '$group_name' is not empty. It contains user(s): $user_list")
            fi
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
        printf '%s\n' "1. Create a dedicated, empty group for 'su' access:"
        printf '%s\n' "# pw groupadd sugroup"
        printf '%s\n' "2. Edit '$PAM_SU_FILE' and add the following line:"
        printf '%s\n' "auth    requisite    pam_group.so no_warn group=sugroup root_only fail_safe ruser"
    fi
}
BASH
    ],

    [ 'id' => '4.4', 'title' => 'Configure Pluggable Authentication Modules', 'type' => 'header'],

    [ 'id' => '4.4.1', 'title' => 'Configure pluggable module arguments', 'type' => 'header'],

    [ 'id' => '4.4.1.1', 'title' => 'Configure pam_passwdqc module', 'type' => 'header'],

    [ 'id' => '4.4.1.1.1', 'title' => 'Ensure password length is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PAM_DIR="/etc/pam.d"
    MIN_LENGTH=14
    FOUND_CONFIG="false"

    config_lines=$(grep -E '^\s*password\s+.*\s+minlen=' $PAM_DIR/* 2>/dev/null)

    if [ -z "$config_lines" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No 'minlen' parameter found in any password-related PAM configuration.")
    else
        echo "$config_lines" | while IFS= read -r line; do
            FOUND_CONFIG="true"
            file_path=$(echo "$line" | cut -d: -f1)
            
            # Extract the N1 value (the number after the first comma in minlen=...)
            minlen_val=$(echo "$line" | sed -n 's/.*minlen=[^,]*,//p' | cut -d, -f1)

            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found config in '$file_path': minlen N1 value is '$minlen_val'.")

            if [ -n "$minlen_val" ] && [ "$minlen_val" -ge "$MIN_LENGTH" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Configuration in '$file_path' is compliant (minlen=$minlen_val).")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration in '$file_path' is not compliant (minlen N1 is '$minlen_val', should be >= $MIN_LENGTH).")
            fi
        done
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
        printf '%s\n' "Ensure a 'password' line in a relevant PAM file (e.g., /etc/pam.d/passwd)"
        printf '%s\n' "contains a 'minlen' parameter with the first value set to 14 or more."
        printf '%s\n' "Example using pam_passwdqc.so:"
        printf '%s\n' "password    requisite    pam_passwdqc.so    enforce=everyone minlen=disabled,14,12,8,6"
    fi
}
BASH
    ],

    [ 'id' => '4.4.1.1.2', 'title' => 'Ensure password quality is enforced for the root user', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PAM_DIR="/etc/pam.d"

    config_lines=$(grep -E '^\s*password\s+(requisite|required|sufficient)\s+pam_passwdqc\.so' $PAM_DIR/* 2>/dev/null)

    if [ -z "$config_lines" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - pam_passwdqc.so is not configured for password checks in any '$PAM_DIR' file.")
    else
        if echo "$config_lines" | grep -q 'enforce=everyone'; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Password quality is correctly enforced for all users, including root.")
            found_config=$(echo "$config_lines" | grep 'enforce=everyone')
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found compliant setting in:")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$found_config")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'enforce=everyone' option is not set for pam_passwdqc.so.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found non-compliant setting(s):")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$config_lines")
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
        printf '%s\n' "Edit a relevant PAM configuration file (e.g., /etc/pam.d/passwd) and add or modify"
        printf '%s\n' "the 'pam_passwdqc.so' line to include the 'enforce=everyone' option."
        printf '%s\n' "Example:"
        printf '%s\n' "password    requisite    pam_passwdqc.so    enforce=everyone minlen=disabled,14,12,8,6"
    fi
}
BASH
    ],

    [ 'id' => '4.4.1.2', 'title' => 'Configure pam_unix module', 'type' => 'header'],

    [ 'id' => '4.4.1.2.1', 'title' => 'Ensure pam_unix does not include nullok', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PAM_FILES="/etc/pam.d/passwd /etc/pam.d/system"

    nullok_entries=$(grep -E '^\s*(auth|account|password|session)\s+(requisite|required|sufficient)\s+pam_unix\.so\b' $PAM_FILES 2>/dev/null | grep -E '\bnullok\b')

    if [ -n "$nullok_entries" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'nullok' argument is present for pam_unix.so, which is insecure.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following non-compliant entries:")
        
        echo "$nullok_entries" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No 'nullok' argument found for pam_unix.so in critical PAM files.")
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
        printf '%s\n' "Edit the identified file(s) and remove the 'nullok' argument"
        printf '%s\n' "from any line containing 'pam_unix.so'."
        printf '%s\n' "Example command to remove it from all PAM files (use with caution):"
        printf '%s\n' "# find /etc/pam.d -type f -exec sed -i '' -E 's|[[:space:]]?nullok||g' {} \\;"
    fi
}
BASH
    ],


    [ 'id' => '4.5', 'title' => 'User Accounts and Environment', 'type' => 'header'],

    [ 'id' => '4.5.1', 'title' => 'Configure shadow password suite parameters', 'type' => 'header'],

    [ 'id' => '4.5.1.1', 'title' => 'Ensure strong password hashing algorithm is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/login.conf"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        if grep -q -E "^\s*:passwd_format=sha512:\\?\s*$" "$CONFIG_FILE"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Password hashing algorithm is correctly set to 'sha512' in '$CONFIG_FILE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Password hashing algorithm is not set to 'sha512' in '$CONFIG_FILE'.")
        fi
        
        current_setting=$(grep "passwd_format" "$CONFIG_FILE")
        if [ -n "$current_setting" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current setting found: $current_setting")
        else
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - No 'passwd_format' setting found in the file.")
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
        printf '%s\n' "1. Edit '$CONFIG_FILE' and add or modify the default entry to include:"
        printf '%s\n' "   :passwd_format=sha512:"
        printf '%s\n' "2. After saving the file, regenerate the login database:"
        printf '%s\n' "# cap_mkdb /etc/login.conf"
    fi
}
BASH
    ],

    [ 'id' => '4.5.1.2', 'title' => 'Ensure password expiration is 365 days or less', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/login.conf"
    MAX_DAYS=365

    config_line=$(awk '/^default:\\/,/:\\s*$/' "$CONFIG_FILE" | grep ':passwordtime=')

    if [ -z "$config_line" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - ':passwordtime=' is not configured in the default class of '$CONFIG_FILE'.")
    else
        days_value=$(echo "$config_line" | sed -n 's/.*:passwordtime=\([0-9]*\)d:.*/\1/p')
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
        
        if [ -z "$days_value" ]; then
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not parse the numeric value from the 'passwordtime' setting.")
        elif [ "$days_value" -gt 0 ] && [ "$days_value" -le "$MAX_DAYS" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Password expiration is set to '$days_value' days, which is compliant (<= $MAX_DAYS).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Password expiration is set to '$days_value' days, which is not compliant (should be > 0 and <= $MAX_DAYS).")
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
        printf '%s\n' "1. Edit '$CONFIG_FILE' and add or modify the default entry to include:"
        printf '%s\n' "   :passwordtime=365d:"
        printf '%s\n' "2. After saving the file, regenerate the login database:"
        printf '%s\n' "# cap_mkdb /etc/login.conf"
        printf '%s\n' "3. Update existing users to enforce the new policy."
    fi
}
BASH
    ],

    [ 'id' => '4.5.1.3', 'title' => 'Ensure password expiration warning days is 7 or more', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/login.conf"
    MIN_DAYS=7

    config_line=$(awk '/^default:\\/,/:\\s*$/' "$CONFIG_FILE" | grep ':warnpassword=')

    if [ -z "$config_line" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - ':warnpassword=' is not configured in the default class of '$CONFIG_FILE'.")
    else
        days_value=$(echo "$config_line" | sed -n 's/.*:warnpassword=\([0-9]*\)d:.*/\1/p')
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
        
        if [ -z "$days_value" ]; then
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not parse the numeric value from the 'warnpassword' setting.")
        elif [ "$days_value" -ge "$MIN_DAYS" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Password warning is set to '$days_value' days, which is compliant (>= $MIN_DAYS).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Password warning is set to '$days_value' days, which is not compliant (should be >= $MIN_DAYS).")
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
        printf '%s\n' "1. Edit '$CONFIG_FILE' and add or modify the default entry to include:"
        printf '%s\n' "   :warnpassword=7d:"
        printf '%s\n' "2. After saving the file, regenerate the login database:"
        printf '%s\n' "# cap_mkdb /etc/login.conf"
    fi
}
BASH
    ],

    [ 'id' => '4.5.2', 'title' => 'Configure root and system accounts and environment', 'type' => 'header'],

    [ 'id' => '4.5.2.1', 'title' => 'Ensure default group for the root account is GID 0', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PASSWD_FILE="/etc/passwd"
    EXPECTED_GID="0"

    root_entry=$(grep '^root:' "$PASSWD_FILE")
    
    if [ -z "$root_entry" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Could not find the 'root' user entry in '$PASSWD_FILE'.")
    else
        current_gid=$(echo "$root_entry" | cut -d: -f4)
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found entry for root: $root_entry")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current GID for root is: $current_gid")

        if [ "$current_gid" = "$EXPECTED_GID" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The root account's default group ID is correctly set to '0'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The root account's default group ID is '$current_gid', but should be '0'.")
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
        printf '%s\n' "Run the following command to set the root user's default group ID to 0:"
        printf '%s\n' "# pw usermod -g 0 root"
    fi
}
BASH
    ],

    [ 'id' => '4.5.2.2', 'title' => 'Ensure root user umask is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    FILES_TO_CHECK="/root/.profile /root/.shrc"

    non_compliant_umasks=$(grep -Esi '^\s*umask\s+0[0-1][0-7]' $FILES_TO_CHECK 2>/dev/null; grep -Esi '^\s*umask\s+02[0-6]' $FILES_TO_CHECK 2>/dev/null)

    if [ -n "$non_compliant_umasks" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - An insecure umask was found. It should be 027 or more restrictive.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following non-compliant entries:")
        
        echo "$non_compliant_umasks" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No insecure umask settings were found for the root user.")
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
        printf '%s\n' "Edit /root/.profile and /root/.shrc to set the umask to 027 or a more restrictive value."
        printf '%s\n' "Example:"
        printf '%s\n' "umask 027"
    fi
}
BASH
    ],

    [ 'id' => '4.5.2.3', 'title' => 'Ensure system accounts are secured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    non_compliant_shells=$(awk -F: '($1!~/^(root|toor|uucp)$/ || $3 == 65533) && $7!~/^(\/usr)?\/sbin\/nologin$/ { print $1 }' /etc/passwd)

    if [ -n "$non_compliant_shells" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following system account(s) do not have a 'nologin' shell:")
        for user in $non_compliant_shells; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $user")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All critical system accounts have a 'nologin' shell.")
    fi

    active_passwords=$(awk -F: '($2!="*" && $7~/^(\/usr)?\/sbin\/nologin$/) { print $1 }' /etc/master.passwd)
    
    if [ -n "$active_passwords" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following 'nologin' account(s) have an active password and should be locked:")
        for user in $active_passwords; do
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $user")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All accounts with a 'nologin' shell have their passwords correctly disabled.")
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
        printf '%s\n' "For system accounts with an incorrect shell, run:"
        printf '%s\n' "# pw usermod <username> -s /usr/sbin/nologin"
        printf '%s\n' ""
        printf '%s\n' "For 'nologin' accounts with an active password, run:"
        printf '%s\n' "# pw lock <username>"
    fi
}
BASH
    ],

    [ 'id' => '4.5.3', 'title' => 'Configure user default environment', 'type' => 'header'],

    [ 'id' => '4.5.3.1', 'title' => 'Ensure nologin is not listed in /etc/shells', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SHELLS_FILE="/etc/shells"

    if [ ! -f "$SHELLS_FILE" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - File '$SHELLS_FILE' not found.")
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Cannot verify contents because '$SHELLS_FILE' does not exist.")
    else
        nologin_entry=$(grep '/nologin\b' "$SHELLS_FILE")

        if [ -n "$nologin_entry" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '/sbin/nologin' or '/usr/sbin/nologin' is listed as a valid shell in '$SHELLS_FILE'.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found non-compliant entry: $nologin_entry")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'nologin' is correctly not listed in '$SHELLS_FILE'.")
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
        printf '%s\n' "Edit the '$SHELLS_FILE' file and remove any line that contains '/sbin/nologin' or '/usr/sbin/nologin'."
    fi
}
BASH
    ],

    [ 'id' => '4.5.3.2', 'title' => 'Ensure default user umask is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    
    is_compliant() {
        umask_val=$1
        
        second_digit=$(echo "$umask_val" | cut -c2)
        third_digit=$(echo "$umask_val" | cut -c3)

        if [ "$second_digit" -ge 2 ] && [ "$third_digit" -ge 2 ]; then
            return 0 # Compliant
        else
            return 1 # Not compliant
        fi
    }

    login_conf_umask=$(grep '^\s*:umask=' /etc/login.conf | tail -n 1 | cut -d= -f2 | tr -d ':')
    if [ -n "$login_conf_umask" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found umask in /etc/login.conf: $login_conf_umask")
        if is_compliant "$login_conf_umask"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Umask in /etc/login.conf is compliant.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Umask in /etc/login.conf ('$login_conf_umask') is not compliant.")
        fi
    fi

    profile_umask=$(grep '^\s*umask' /etc/profile | tail -n 1 | awk '{print $2}')
    if [ -n "$profile_umask" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found umask in /etc/profile: $profile_umask")
        if is_compliant "$profile_umask"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Umask in /etc/profile is compliant.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Umask in /etc/profile ('$profile_umask') is not compliant.")
        fi
    fi

    cshrc_umask=$(grep '^\s*umask' /etc/csh.cshrc | tail -n 1 | awk '{print $2}')
    if [ -n "$cshrc_umask" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found umask in /etc/csh.cshrc: $cshrc_umask")
        if is_compliant "$cshrc_umask"; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Umask in /etc/csh.cshrc is compliant.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Umask in /etc/csh.cshrc ('$cshrc_umask') is not compliant.")
        fi
    fi

    if [ -z "$login_conf_umask" ] && [ -z "$profile_umask" ] && [ -z "$cshrc_umask" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No system-wide umask setting was found in /etc/login.conf, /etc/profile, or /etc/csh.cshrc.")
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
        printf '%s\n' "Ensure a default umask of 022 or more restrictive (e.g., 027) is set"
        printf '%s\n' "in a system-wide configuration file like /etc/login.conf or /etc/profile."
        printf '%s\n' "Example for /etc/login.conf:"
        printf '%s\n' "  :umask=022:"
        printf '%s\n' "Example for /etc/profile:"
        printf '%s\n' "umask 022"
    fi
}
BASH
    ],
];
