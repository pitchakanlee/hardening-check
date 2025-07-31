<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.5.pdf
// =============================================================
return [
    [ 'id' => '5', 'title' => 'Logging and Auditing', 'type' => 'header'],

    [ 'id' => '5.1', 'title' => 'Configure Logging', 'type' => 'header'],

    [ 'id' => '5.1.1', 'title' => 'Configure syslog', 'type' => 'header'],

    [ 'id' => '5.1.1.1', 'title' => 'Ensure syslog is installed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="syslogd_enable"
    EXPECTED_VALUE="YES"

    syslogd_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $syslogd_status")

    if [ "$syslogd_status" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is correctly enabled.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is set to '$syslogd_status', but should be '$EXPECTED_VALUE'.")
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
        printf '%s\n' "Run the following command to start and enable the syslogd service:"
        printf '%s\n' "# service syslogd start"
        printf '%s\n' "# sysrc syslogd_enable=YES"
    fi
}
BASH
    ],

    [ 'id' => '5.1.1.2', 'title' => 'Ensure syslogd service is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="syslogd_enable"
    EXPECTED_VALUE="YES"
    SERVICE_NAME="syslogd"

    syslogd_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $syslogd_status")

    if [ "$syslogd_status" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is correctly enabled.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is set to '$syslogd_status', but should be '$EXPECTED_VALUE'.")
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
        printf '%s\n' "Run the following command to enable the syslogd service:"
        printf '%s\n' "# service $SERVICE_NAME enable"
    fi
}
BASH
    ],

    [ 'id' => '5.1.1.3', 'title' => 'Ensure syslogd default file permissions are configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILES="/etc/newsyslog.conf /etc/newsyslog.conf.d/*"

    non_compliant_lines=$(grep -shE '^\s*[^#]' $CONFIG_FILES 2>/dev/null | awk '
        NF>=2 {
            perms = $2;
            perms_str = sprintf("%03d", perms);
            group_perm = substr(perms_str, 2, 1);
            other_perm = substr(perms_str, 3, 1);
            
            is_group_writable=0;
            is_other_writable=0;

            if (group_perm == "2" || group_perm == "3" || group_perm == "6" || group_perm == "7") {
                is_group_writable=1;
            }
            if (other_perm == "2" || other_perm == "3" || other_perm == "6" || other_perm == "7") {
                is_other_writable=1;
            }
            
            if (is_group_writable == 1 || is_other_writable == 1) {
                print FILENAME ": " $0;
            }
        }
    ')

    if [ -n "$non_compliant_lines" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - One or more log files have permissions less restrictive than 644.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following non-compliant entries:")
        
        echo "$non_compliant_lines" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All log file permissions in newsyslog configurations are compliant (644 or more restrictive).")
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
        printf '%s\n' "Edit the identified file(s) and ensure the mode for each log file is set to 644, 640, or 600."
        printf '%s\n' "After editing, reload the service:"
        printf '%s\n' "# service syslogd reload"
    fi
}
BASH
    ],

    [ 'id' => '5.1.1.4', 'title' => 'Ensure logging is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Please review the following logging configurations against your site policy."

    if [ -f "/etc/syslog.conf" ]; then
        printf '\n%s\n' "--- Content of /etc/syslog.conf ---"
        cat /etc/syslog.conf
        printf '%s\n' "-------------------------------------"
    fi

    if [ -d "/etc/syslog.d" ] && [ -n "$(ls -A /etc/syslog.d/)" ]; then
        printf '\n%s\n' "--- Content of /etc/syslog.d/ ---"
        for file in /etc/syslog.d/*; do
            if [ -f "$file" ]; then
                printf '\n%s\n' "### $file ###"
                cat "$file"
            fi
        done
        printf '%s\n' "---------------------------------"
    fi

    if [ -d "/usr/local/etc/syslog.d" ] && [ -n "$(ls -A /usr/local/etc/syslog.d/)" ]; then
        printf '\n%s\n' "--- Content of /usr/local/etc/syslog.d/ ---"
        for file in /usr/local/etc/syslog.d/*; do
            if [ -f "$file" ]; then
                printf '\n%s\n' "### $file ###"
                cat "$file"
            fi
        done
        printf '%s\n' "-------------------------------------------"
    fi

    printf '\n%s\n' "--- Current State of /var/log ---"
    ls -l /var/log/
    printf '%s\n' "---------------------------------"
    
    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "Review and edit /etc/syslog.conf and files in /etc/syslog.d/ to match"
    printf '%s\n' "your environment's logging requirements. After making changes, reload the service:"
    printf '%s\n' "# service syslogd reload"

    printf '\n%s\n' "** MANUAL ** Required: Manually review the configurations above against your site policy."
}
BASH
    ],

    [ 'id' => '5.1.1.5', 'title' => 'Ensure syslog is configured to send logs to a remote log host', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILES="/etc/syslog.conf /etc/syslog.d/* /usr/local/etc/syslog.d/*"

    remote_log_config=$(grep -hE '^\s*[^#;]+\s+@' $CONFIG_FILES 2>/dev/null)

    if [ -n "$remote_log_config" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Remote logging appears to be configured.")
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found the following remote logging entries:")
        
        echo "$remote_log_config" | while IFS= read -r line; do
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "    $line")
        done
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No configuration found to send logs to a remote host.")
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
        printf '%s\n' "Edit /etc/syslog.conf and add a line to forward logs to your central log host."
        printf '%s\n' "Example:"
        printf '%s\n' "*.* @loghost.example.com"
        printf '%s\n' "Then, reload the syslogd configuration:"
        printf '%s\n' "# service syslogd reload"
    fi
}
BASH
    ],

    [ 'id' => '5.1.1.6', 'title' => 'Ensure rsyslog is not configured to receive logs from a remote client', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="syslogd_flags"

    syslogd_flags=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $syslogd_flags")

    case "$syslogd_flags" in
        *-s*)
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' contains the '-s' flag, which prevents receiving remote logs.")
            ;;
        *)
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' does not contain the '-s' flag.")
            ;;
    esac

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
        printf '%s\n' "To prevent syslogd from receiving remote logs, run:"
        printf '%s\n' "# sysrc syslogd_flags+=\" -s\""
        printf '%s\n' "Then, restart the service:"
        printf '%s\n' "# service syslogd restart"
    fi
}
BASH
    ],

    [ 'id' => '5.1.2', 'title' => 'Ensure newsyslog is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Please review the following newsyslog configurations against your site policy."

    if [ -f "/etc/newsyslog.conf" ]; then
        printf '\n%s\n' "--- Content of /etc/newsyslog.conf ---"
        cat /etc/newsyslog.conf
        printf '%s\n' "----------------------------------------"
    fi

    if [ -d "/etc/newsyslog.conf.d" ] && [ -n "$(ls -A /etc/newsyslog.conf.d/)" ]; then
        printf '\n%s\n' "--- Content of /etc/newsyslog.conf.d/ ---"
        for file in /etc/newsyslog.conf.d/*; do
            if [ -f "$file" ]; then
                printf '\n%s\n' "### $file ###"
                cat "$file"
            fi
        done
        printf '%s\n' "-----------------------------------------"
    fi

    if [ -d "/usr/local/etc/newsyslog.conf.d" ] && [ -n "$(ls -A /usr/local/etc/newsyslog.conf.d/)" ]; then
        printf '\n%s\n' "--- Content of /usr/local/etc/newsyslog.conf.d/ ---"
        for file in /usr/local/etc/newsyslog.conf.d/*; do
            if [ -f "$file" ]; then
                printf '\n%s\n' "### $file ###"
                cat "$file"
            fi
        done
        printf '%s\n' "-----------------------------------------------"
    fi
    
    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "Review and edit the files listed above to ensure log rotation"
    printf '%s\n' "and permissions match your site policy."

    printf '\n%s\n' "** MANUAL ** Required: Manually review the configurations above against your site policy."
}
BASH
    ],

    [ 'id' => '5.1.3', 'title' => 'Ensure all logfiles have appropriate access configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    log_files="
/var/log/messages:640:root:wheel
/var/log/cron:640:root:wheel
/var/log/auth.log:640:root:wheel
/var/log/maillog:640:root:wheel
/var/log/xferlog:640:root:wheel
/var/log/debug.log:640:root:wheel
/var/log/console.log:640:root:wheel
/var/log/dmesg:640:root:wheel
/var/log/security:640:root:wheel
/var/log/all.log:640:root:wheel
"
    echo "$log_files" | while IFS=: read -r file expected_perms expected_user expected_group; do
        if [ -z "$file" ]; then continue; fi
        
        if [ -f "$file" ]; then
            current_perms=$(stat -f "%Lp" "$file")
            current_user=$(stat -f "%Su" "$file")
            current_group=$(stat -f "%Sg" "$file")

            if [ "$current_perms" -gt "$expected_perms" ]; then
                echo "$file permission is set to $current_perms but should be $expected_perms or more restrictive"
            fi

            if [ "$current_user" != "$expected_user" ]; then
                echo "$file ownership is set to $current_user but should be $expected_user"
            fi

            if [ "$current_group" != "$expected_group" ]; then
                echo "$file group ownership is set to $current_group but should be $expected_group"
            fi
        else
            echo "File $file does not exist."
        fi
    done

    printf '\n%s\n' "** MANUAL ** Required: Manually review the findings above."
}
BASH
    ],

    [ 'id' => '5.2', 'title' => 'Configure System Accounting (auditd)', 'type' => 'header'],

    [ 'id' => '5.2.1', 'title' => 'Ensure auditing is enabled', 'type' => 'header'],

    [ 'id' => '5.2.1.1', 'title' => 'Ensure auditd service is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="auditd_enable"
    EXPECTED_VALUE="YES"
    SERVICE_NAME="auditd"

    auditd_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $auditd_status")

    if [ "$auditd_status" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is correctly enabled.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is set to '$auditd_status', but should be '$EXPECTED_VALUE'.")
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
        printf '%s\n' "Run the following commands to enable and start the auditd service:"
        printf '%s\n' "# service $SERVICE_NAME enable"
        printf '%s\n' "# service $SERVICE_NAME start"
    fi
}
BASH
    ],

    [ 'id' => '5.2.2', 'title' => 'Configure Data Retention', 'type' => 'header'],

    [ 'id' => '5.2.2.1', 'title' => 'Ensure audit log storage size is configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*filesz:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'filesz' parameter is configured.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'filesz' parameter is not configured in '$CONFIG_FILE'.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
        printf '%s\n' "  - Please verify the configured size meets your site policy."
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Edit '$CONFIG_FILE' and add or modify the 'filesz' parameter to match your site policy."
        printf '%s\n' "Example:"
        printf '%s\n' "filesz:2M"
    fi
}
BASH
    ],

    [ 'id' => '5.2.2.2', 'title' => 'Ensure audit logs are not automatically deleted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*expire-after:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'expire-after' parameter is configured.")
        else
            # If the line doesn't exist, logs are never deleted, which is compliant.
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'expire-after' parameter is not configured, so logs are not automatically deleted.")
        fi
    fi

    if [ -n "$OUTPUT_INFO" ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "$OUTPUT_INFO"
    fi

    if [ -z "$OUTPUT_FAIL" ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "$OUTPUT_PASS"
        printf '%s\n' "  - Please verify the configured value meets your site policy for log retention."
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "$OUTPUT_FAIL"
    fi
    
    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "Review the 'expire-after' directive in '$CONFIG_FILE'."
    printf '%s\n' "To retain logs for a longer period, increase the value or remove the line entirely."
    printf '%s\n' "Example for 60 days AND 1GB disk space limit:"
    printf '%s\n' "expire-after: 60d AND 1G"
}
BASH
    ],

    [ 'id' => '5.2.3', 'title' => 'Configure auditd rules', 'type' => 'header'],

    [ 'id' => '5.2.3.1', 'title' => 'Ensure actions as another user are always logged', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'aa'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'aa' flag is correctly configured, ensuring sudo events are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'aa' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add or modify the 'flags' line to include 'aa'."
        printf '%s\n' "Example:"
        printf '%s\n' "flags:lo,aa"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.2', 'title' => 'Ensure events that modify the sudo log file are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"
    
    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -z "$config_line" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
        else
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            required_flags="fa fc fd fm fr fw"
            
            for flag in $required_flags; do
                if echo "$config_line" | grep -q -E "(^|,) *$flag *(,|$)"; then
                    OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Required flag '$flag' is present.")
                else
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line is missing the required '$flag' event class.")
                fi
            done
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the required file event classes to the 'flags' line."
        printf '%s\n' "Example (add these to any existing flags):"
        printf '%s\n' "flags:lo,aa,fa,fc,fd,fm,fr,fw"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.3', 'title' => 'Ensure use of privileged commands are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'pc'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'pc' flag is correctly configured, ensuring privileged commands are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'pc' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'pc' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa,pc"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.4', 'title' => 'Ensure discretionary access control permission modification events are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'fm'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'fm' flag is correctly configured, ensuring file attribute modifications are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'fm' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'fm' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa,fm"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.5', 'title' => 'Ensure successful file system mounts are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'ad'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'ad' flag is correctly configured, ensuring administrative events are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'ad' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'ad' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa,ad"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.6', 'title' => 'Ensure login and logout events are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'lo'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'lo' flag is correctly configured, ensuring login/logout events are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'lo' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'lo' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.7', 'title' => 'Ensure file deletion events by users are collected', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'fd'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'fd' flag is correctly configured, ensuring file deletion events are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'fd' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'fd' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa,fd"
    fi
}
BASH
    ],

    [ 'id' => '5.2.3.8', 'title' => 'Ensure successful and unsuccessful attempts to use the usermod command are recorded', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    if [ ! -f "$CONFIG_FILE" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Configuration file '$CONFIG_FILE' not found.")
    else
        config_line=$(grep '^\s*flags:' "$CONFIG_FILE")

        if [ -n "$config_line" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found setting: $config_line")
            
            if echo "$config_line" | grep -q 'ad'; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The 'ad' flag is correctly configured, ensuring administrative events are logged.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' line does not contain the required 'ad' event class.")
            fi
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'flags' parameter is not configured in '$CONFIG_FILE'.")
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
        printf '%s\n' "Edit '$CONFIG_FILE' and add the 'ad' event class to the 'flags' line."
        printf '%s\n' "Example (add this to any existing flags):"
        printf '%s\n' "flags:lo,aa,ad"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4', 'title' => 'Configure auditd file access', 'type' => 'header'],

    [ 'id' => '5.2.4.1', 'title' => 'Ensure the audit log directory is 0750 or more restrictive', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    audit_dir=$(grep '^\s*dir:' "$CONFIG_FILE" | awk -F: '{print $2}' | xargs)

    if [ -z "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'dir' parameter is not set in '$CONFIG_FILE'.")
    elif [ ! -d "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured audit log directory '$audit_dir' does not exist.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found audit log directory: $audit_dir")
        
        perms_symbolic=$(stat -f '%Sp' "$audit_dir") # e.g., "drwxr-x---"
        
        group_perms=$(echo "$perms_symbolic" | cut -c 5-7)
        other_perms=$(echo "$perms_symbolic" | cut -c 8-10)

        is_compliant="true"
        
        case "$group_perms" in
            *w*)
                is_compliant="false"
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Directory permissions ('$perms_symbolic') are not compliant: Group has write permission.")
                ;;
        esac

        if [ "$other_perms" != "---" ]; then
            is_compliant="false"
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Directory permissions ('$perms_symbolic') are not compliant: Other has permissions.")
        fi

        if [ "$is_compliant" = "true" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Directory permissions '$perms_symbolic' for '$audit_dir' are compliant (0750 or more restrictive).")
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
        printf '%s\n' "Run the following command to set correct permissions on the audit log directory:"
        printf '%s\n' "# chmod g-w,o-rwx \"\$(awk -F\":\" '/^dir/ {print \$2}' /etc/security/audit_control)\""
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.2', 'title' => 'Ensure audit log files are mode 0640 or less permissive', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    audit_dir=$(grep '^\s*dir:' "$CONFIG_FILE" | awk -F: '{print $2}' | xargs)

    if [ -z "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'dir' parameter is not set in '$CONFIG_FILE'.")
    elif [ ! -d "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured audit log directory '$audit_dir' does not exist.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing all files within: $audit_dir")
        
        non_compliant_files=$(find "$audit_dir" -type f -perm -027 -print)

        if [ -n "$non_compliant_files" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following audit log files have permissions more permissive than 0640:")
            echo "$non_compliant_files" | while IFS= read -r file; do
                perms=$(stat -f '%Lp' "$file")
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file (Permissions: $perms)")
            done
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All audit log files in '$audit_dir' have compliant permissions (0640 or more restrictive).")
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
        printf '%s\n' "Run the following command to set correct permissions on all audit log files:"
        printf '%s\n' "# chmod g-w,o-rwx \"\$(awk -F\":\" '/^dir/ {print \$2}' /etc/security/audit_control)\"/*"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.3', 'title' => 'Ensure only authorized users own audit log files', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    audit_dir=$(grep '^\s*dir:' "$CONFIG_FILE" | awk -F: '{print $2}' | xargs)

    if [ -z "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'dir' parameter is not set in '$CONFIG_FILE'.")
    elif [ ! -d "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured audit log directory '$audit_dir' does not exist.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing all files within: $audit_dir")
        
        non_compliant_files=$(find "$audit_dir" -type f ! -user root -print)

        if [ -n "$non_compliant_files" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following audit log files are not owned by root:")
            echo "$non_compliant_files" | while IFS= read -r file; do
                owner=$(stat -f '%Su' "$file")
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file (Owned by: $owner)")
            done
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All audit log files in '$audit_dir' are correctly owned by root.")
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
        printf '%s\n' "Run the following command to set the correct owner on all audit log files:"
        printf '%s\n' "# find \"\$(awk -F\":\" '/^dir/ {print \$2}' /etc/security/audit_control)\" -type f ! -user root -exec chown root {} +"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.4', 'title' => 'Ensure only authorized groups are assigned ownership of audit log files', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => true, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/security/audit_control"

    audit_dir=$(grep '^\s*dir:' "$CONFIG_FILE" | awk -F: '{print $2}' | xargs)

    if [ -z "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The 'dir' parameter is not set in '$CONFIG_FILE'.")
    elif [ ! -d "$audit_dir" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The configured audit log directory '$audit_dir' does not exist.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Auditing file group ownership within: $audit_dir")
        
        non_compliant_files=$(find "$audit_dir" -type f ! -group audit ! -group wheel -print)

        if [ -n "$non_compliant_files" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following audit log files are not group-owned by 'audit' or 'wheel':")
            echo "$non_compliant_files" | while IFS= read -r file; do
                group=$(stat -f '%Sg' "$file")
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file (Group: $group)")
            done
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All audit log files in '$audit_dir' are correctly group-owned by 'audit' or 'wheel'.")
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
        printf '%s\n' "Run the following command to set the correct group on the audit log directory and its contents:"
        printf '%s\n' "# chgrp audit \"\$(awk -F\":\" '/^dir/ {print \$2}' /etc/security/audit_control)\""
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.5', 'title' => 'Ensure audit configuration files are restrictive', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    config_files="
/etc/security/audit_warn:500
/etc/security/audit_event:444
/etc/security/audit_class:444
/etc/security/audit_control:600
/etc/security/audit_user:600
"
    echo "$config_files" | while IFS=: read -r file expected_perms; do
        if [ -z "$file" ]; then continue; fi

        if [ ! -f "$file" ]; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - File $file does not exist, skipping check.")
            continue
        fi
        
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking: $file")
        
        current_perms=$(stat -f "%Lp" "$file")
        
        if [ "$current_perms" -le "$expected_perms" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file: Permissions '$current_perms' are compliant (<= $expected_perms).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file: Permissions are '$current_perms', but should be '$expected_perms' or more restrictive.")
        fi
    done

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
        printf '%s\n' "Run the following commands to set the recommended permissions:"
        printf '%s\n' "# chmod 444 /etc/security/audit_event /etc/security/audit_class"
        printf '%s\n' "# chmod 500 /etc/security/audit_warn"
        printf '%s\n' "# chmod 600 /etc/security/audit_control /etc/security/audit_user"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.6', 'title' => 'Ensure audit configuration files are owned by root', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_DIR="/etc/security"

    non_compliant_files=$(find "$CONFIG_DIR" -type f -name 'audit*' ! -user root -print)

    if [ -n "$non_compliant_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following audit configuration files are not owned by root:")
        echo "$non_compliant_files" | while IFS= read -r file; do
            owner=$(stat -f '%Su' "$file")
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file (Owned by: $owner)")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All audit configuration files in '$CONFIG_DIR' are correctly owned by root.")
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
        printf '%s\n' "Run the following command to set the correct owner on all audit configuration files:"
        printf '%s\n' "# find /etc/security -type f -name 'audit*' ! -user root -exec chown root {} +"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.7', 'title' => 'Ensure audit configuration files belong to group wheel', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_DIR="/etc/security"

    non_compliant_files=$(find "$CONFIG_DIR" -type f -name 'audit*' ! -group wheel -print)

    if [ -n "$non_compliant_files" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The following audit configuration files are not group-owned by wheel:")
        echo "$non_compliant_files" | while IFS= read -r file; do
            group=$(stat -f '%Sg' "$file")
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "    - $file (Group: $group)")
        done
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - All audit configuration files in '$CONFIG_DIR' are correctly group-owned by wheel.")
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
        printf '%s\n' "Run the following command to set the correct group on all audit configuration files:"
        printf '%s\n' "# find /etc/security -type f -name 'audit*' ! -group wheel -exec chgrp wheel {} +"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.8', 'title' => 'Ensure audit tools are 555 or more restrictive', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    AUDIT_TOOLS_PATH="/usr/sbin/*audit*"
    
    found_tools=0
    for file_path in $AUDIT_TOOLS_PATH; do
        if [ -f "$file_path" ]; then
            found_tools=1
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking: $file_path")
            
            perms_symbolic=$(stat -f '%Sp' "$file_path")
            
            case "$perms_symbolic" in
                *w*)
                    OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Permissions are '$perms_symbolic', but should not include write permissions.")
                    ;;
                *)
                    OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Permissions '$perms_symbolic' are compliant (no write bits).")
                    ;;
            esac
        fi
    done

    if [ "$found_tools" -eq 0 ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No audit tools found in /usr/sbin/ to check.")
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
        printf '%s\n' "Run the following command to set correct permissions on the audit tools:"
        printf '%s\n' "# chmod 555 /usr/sbin/*audit*"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.9', 'title' => 'Ensure audit tools are owned by root', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    AUDIT_TOOLS_PATH="/usr/sbin/*audit*"
    
    found_tools=0
    for file_path in $AUDIT_TOOLS_PATH; do
        if [ -f "$file_path" ]; then
            found_tools=1
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking owner for: $file_path")
            
            owner=$(stat -f '%Su' "$file_path")
            
            if [ "$owner" = "root" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Owner is correctly set to 'root'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Owner is '$owner', but should be 'root'.")
            fi
        fi
    done

    if [ "$found_tools" -eq 0 ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No audit tools found in /usr/sbin/ to check.")
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
        printf '%s\n' "Run the following command to set the correct owner on the audit tools:"
        printf '%s\n' "# chown root /usr/sbin/*audit*"
    fi
}
BASH
    ],

    [ 'id' => '5.2.4.10', 'title' => 'Ensure audit tools belong to group wheel', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    AUDIT_TOOLS_PATH="/usr/sbin/*audit*"
    
    found_tools=0
    for file_path in $AUDIT_TOOLS_PATH; do
        if [ -f "$file_path" ]; then
            found_tools=1
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Checking group for: $file_path")
            
            group=$(stat -f '%Sg' "$file_path")
            
            if [ "$group" = "wheel" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - $file_path: Group is correctly set to 'wheel'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - $file_path: Group is '$group', but should be 'wheel'.")
            fi
        fi
    done

    if [ "$found_tools" -eq 0 ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No audit tools found in /usr/sbin/ to check.")
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
        printf '%s\n' "Run the following command to set the correct group on the audit tools:"
        printf '%s\n' "# chgrp wheel /usr/sbin/*audit*"
    fi
}
BASH
    ],

    [ 'id' => '5.3', 'title' => 'Configure Integrity Checking', 'type' => 'header'],

    [ 'id' => '5.3.1', 'title' => 'Ensure AIDE is installed', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PACKAGE_NAME="aide"

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
        printf '%s\n' "Run the following command to install AIDE:"
        printf '%s\n' "# pkg install aide"
        printf '%s\n' "After installation, initialize the AIDE database:"
        printf '%s\n' "# aide --init"
        printf '%s\n' "# mv /var/db/aide/aide.db.new /var/db/aide/aide.db"
    fi
}
BASH
    ],

    [ 'id' => '5.3.2', 'title' => 'Ensure filesystem integrity is regularly checked', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PACKAGE_NAME="aide"

    if ! pkg query -g %n "$PACKAGE_NAME" >/dev/null 2>&1; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Prerequisite FAIL: Package '$PACKAGE_NAME' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package 'aide' is installed. Checking for a scheduled cron job...")
        
        aide_cron_job=$(crontab -l -u root 2>/dev/null | grep -E 'aide\s+.*--check')

        if [ -n "$aide_cron_job" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - A cron job to run 'aide --check' is scheduled for the root user.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found cron job: $aide_cron_job")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No cron job is scheduled for the root user to run 'aide --check'.")
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
        printf '%s\n' "If AIDE is not installed, run: # pkg install aide"
        printf '%s\n' "Then, add a cron job for the root user by running 'crontab -u root -e' and adding a line like:"
        printf '%s\n' "@daily /usr/local/bin/aide --check"
    fi
}
BASH
    ],
];
