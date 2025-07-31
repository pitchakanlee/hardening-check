<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.2.pdf
// =============================================================
return [
    [ 'id' => '2', 'title' => 'Services', 'type' => 'header'],

    ['id' => '2.1', 'title' => 'Configure Time Synchronization', 'type' => 'header'],

    [ 'id' => '2.1.1', 'title' => 'Ensure time synchronization is in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    SERVICE_NAME="ntpd"

    if service "$SERVICE_NAME" status >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Time synchronization daemon '$SERVICE_NAME' is running.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Time synchronization daemon '$SERVICE_NAME' is not running.")
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
        printf '%s\n' "Run the following commands to enable and start the ntpd service:"
        printf '%s\n' "# service ntpd enable"
        printf '%s\n' "# sysrc ntpd_sync_on_start=\"YES\""
        printf '%s\n' "# service ntpd start"
    fi
}
BASH
    ],

    [ 'id' => '2.2', 'title' => 'Configure Special Purpose Services', 'type' => 'header'],

    [ 'id' => '2.2.1', 'title' => 'Ensure autofs services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="autofs_enable"

    autofs_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $autofs_status")

    if [ "$autofs_status" = "YES" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is enabled.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is not enabled ('$autofs_status').")
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
        printf '%s\n' "To stop and disable the automount service, run:"
        printf '%s\n' "# service automount onestop"
        printf '%s\n' "# service automount onedisable"
    fi
}
BASH
    ],

    [ 'id' => '2.2.2', 'title' => 'Ensure ftp server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    INETD_CONF="/etc/inetd.conf"
    if [ -f "$INETD_CONF" ]; then
        if grep -q -E '^\s*ftp\s' "$INETD_CONF"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Legacy ftpd service is enabled in '$INETD_CONF'.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Legacy ftpd service is not enabled in '$INETD_CONF'.")
        fi
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - File '$INETD_CONF' does not exist.")
    fi

    if ! pkg query -g %n 'vsftpd*' >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package 'vsftpd' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package 'vsftpd' is installed, checking services...")
        
        vsftpd_status=$(sysrc -n vsftpd_enable)
        if [ "$vsftpd_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'vsftpd_enable' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'vsftpd_enable' is not enabled.")
        fi

        vsftpd6_status=$(sysrc -n vsftpd6_enable)
        if [ "$vsftpd6_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'vsftpd6_enable' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'vsftpd6_enable' is not enabled.")
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
        printf '%s\n' "If FTP services are not required, disable or remove them."
        printf '%s\n' "To disable legacy ftpd: # sed -i '' -e 's|^ftp|#ftp|g' /etc/inetd.conf && service inetd restart"
        printf '%s\n' "To remove vsftpd: # service vsftpd onestop && pkg remove -g 'vsftpd*'"
        printf '%s\n' "To disable vsftpd if required by a dependency:"
        printf '%s\n' "# service vsftpd onestop && service vsftpd onedisable"
    fi
}        
BASH        
    ],

    [ 'id' => '2.2.3', 'title' => 'Ensure message access server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    if ! pkg query -g %n 'dovecot*' >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package 'dovecot' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package 'dovecot' is installed, checking service...")
        dovecot_status=$(sysrc -n dovecot_enable)
        if [ "$dovecot_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'dovecot_enable' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'dovecot_enable' is not enabled.")
        fi
    fi

    if ! pkg query -g %n 'cyrus-imapd*' >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package 'cyrus-imapd' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package 'cyrus-imapd' is installed, checking service...")
        cyrus_status=$(sysrc -n cyrus_imapd_enable)
        if [ "$cyrus_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - 'cyrus_imapd_enable' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - 'cyrus_imapd_enable' is not enabled.")
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
        printf '%s\n' "If packages are not required, run:"
        printf '%s\n' "# service dovecot onestop; service cyrus_imapd onestop"
        printf '%s\n' "# pkg remove -g 'dovecot*' 'cyrus-imapd*'"
        printf '%s\n' ""
        printf '%s\n' "- OR - If a package is required, disable the service:"
        printf '%s\n' "# service dovecot onestop; service dovecot onedisable"
        printf '%s\n' "# service cyrus_imapd onestop; service cyrus_imapd onedisable"
    fi
}
BASH
    ],

    [ 'id' => '2.2.4', 'title' => 'Ensure network file system services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="nfs_server_enable"

    nfsd_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $nfsd_status")

    if [ "$nfsd_status" = "YES" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is enabled.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is not enabled ('$nfsd_status').")
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
        printf '%s\n' "To stop and disable the nfsd service, run:"
        printf '%s\n' "# service nfsd onestop"
        printf '%s\n' "# service nfsd onedisable"
    fi
}        
BASH        
    ],

    [ 'id' => '2.2.5', 'title' => 'Ensure nis server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="nis_server_enable"
    SERVICE_NAME="ypserv"

    nis_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $nis_status")

    if [ "$nis_status" = "YES" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is enabled.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is not enabled ('$nis_status').")
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
        printf '%s\n' "To stop and disable the ypserv service, run:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# service $SERVICE_NAME onedisable"
    fi
}
BASH
    ],

    [ 'id' => '2.2.6', 'title' => 'Ensure rpcbind services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="rpcbind_enable"
    SERVICE_NAME="rpcbind"

    rpcbind_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $rpcbind_status")

    if [ "$rpcbind_status" = "YES" ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is enabled.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is not enabled ('$rpcbind_status').")
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
        printf '%s\n' "To stop and disable the rpcbind service, run:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# service $SERVICE_NAME onedisable"
    fi
}        
BASH        
    ],

    [ 'id' => '2.2.7', 'title' => 'Ensure snmp services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PACKAGE_NAME="net-snmp"
    RC_VAR="snmpd_enable"
    SERVICE_NAME="snmpd"

    if ! pkg query -g %n "$PACKAGE_NAME" >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package '$PACKAGE_NAME' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package '$PACKAGE_NAME' is installed, checking service...")
        
        snmpd_status=$(sysrc -n "$RC_VAR")
        
        if [ "$snmpd_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$RC_VAR' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$RC_VAR' is not enabled ('$snmpd_status').")
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
        printf '%s\n' "If the package is not required, run:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# pkg remove $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required, disable the service:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# service $SERVICE_NAME onedisable"
    fi
}
BASH
    ],

    [ 'id' => '2.2.8', 'title' => 'Ensure telnet server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    PACKAGE_NAME="freebsd-telnetd"
    if pkg query -g %n "$PACKAGE_NAME" >/dev/null 2>&1; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Insecure package '$PACKAGE_NAME' is installed.")
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Insecure package '$PACKAGE_NAME' is not installed.")
    fi

    INETD_CONF="/etc/inetd.conf"
    if [ -f "$INETD_CONF" ]; then
        if grep -q -E '^\s*telnet\s' "$INETD_CONF"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Insecure telnet service is enabled in '$INETD_CONF'.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Insecure telnet service is not enabled in '$INETD_CONF'.")
        fi
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - File '$INETD_CONF' does not exist.")
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
        printf '%s\n' "To disable the legacy telnet service, run:"
        printf '%s\n' "# sed -i '' -e 's|^telnet|#telnet|g' /etc/inetd.conf && service inetd restart"
        printf '%s\n' "To remove the telnet package, run:"
        printf '%s\n' "# pkg remove freebsd-telnetd"
    fi
}        
BASH        
    ],

    [ 'id' => '2.2.9', 'title' => 'Ensure tftp server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    INETD_CONF="/etc/inetd.conf"

    if [ ! -f "$INETD_CONF" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - File '$INETD_CONF' does not exist, so tftp service is not enabled.")
    else
        if grep -q -E '^\s*tftp\s' "$INETD_CONF"; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Insecure tftp service is enabled in '$INETD_CONF'.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Insecure tftp service is not enabled in '$INETD_CONF'.")
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
        printf '%s\n' "To disable the tftp service, run the following commands:"
        printf '%s\n' "# sed -i '' -e 's|^tftp|#tftp|g' /etc/inetd.conf"
        printf '%s\n' "# service inetd restart"
    fi
}
BASH
    ],

    [ 'id' => '2.2.10', 'title' => 'Ensure web proxy server services are not in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    PACKAGE_NAME="squid"
    RC_VAR="squid_enable"
    SERVICE_NAME="squid"

    if ! pkg query -g %n "$PACKAGE_NAME" >/dev/null 2>&1; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Package '$PACKAGE_NAME' is not installed.")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Package '$PACKAGE_NAME' is installed, checking service...")
        
        squid_status=$(sysrc -n "$RC_VAR")
        
        if [ "$squid_status" = "YES" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$RC_VAR' is set to YES in rc.conf.")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - '$RC_VAR' is not enabled ('$squid_status').")
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
        printf '%s\n' "If the package is not required, run:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# pkg remove $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required, disable the service:"
        printf '%s\n' "# service $SERVICE_NAME onestop"
        printf '%s\n' "# service $SERVICE_NAME onedisable"
    fi
}        
BASH        
    ],

    [ 'id' => '2.2.11', 'title' => 'Ensure mail transfer agents are configured for local-only mode', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    listening_services=$(sockstat -46L | grep -E ':25\s|:465\s|:587\s')

    if [ -z "$listening_services" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - No services are listening on standard mail ports (25, 465, 587).")
    else
        non_loopback_listeners=$(echo "$listening_services" | grep -v '127.0.0.1:\|\[::1\]:')
        
        if [ -n "$non_loopback_listeners" ]; then
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - A Mail Transfer Agent is listening on a non-loopback network interface.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - The following services are listening publicly:")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$non_loopback_listeners")
        else
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Mail services are only listening on the loopback interface, which is compliant.")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found services listening locally:")
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "$listening_services")
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
        printf '%s\n' "If using Postfix, edit /usr/local/etc/postfix/main.cf and set:"
        printf '%s\n' "inet_interfaces = localhost"
        printf '%s\n' "Then restart the service:"
        printf '%s\n' "# service postfix restart"
        printf '%s\n' "Note: If using another MTA (like Sendmail), consult its documentation."
    fi
}
BASH
    ],

    [ 'id' => '2.2.12', 'title' => 'Ensure only approved services are listening on a network interface', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Please review the following listening services to ensure they are all required"
    printf '%s\n' "and approved according to your site policy."
    printf '%s\n' "----------------------------------------------------------------------"
    
    sockstat -46L
    
    printf '%s\n' "----------------------------------------------------------------------"
    
    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "For any unapproved service listed above:"
    printf '%s\n' "If the package is not required, run:"
    printf '%s\n' "# service <service_name> onestop"
    printf '%s\n' "# pkg remove <package_name>"
    printf '%s\n' ""
    printf '%s\n' "- OR - If the package is required for a dependency, disable the service:"
    printf '%s\n' "# service <service_name> onestop"
    printf '%s\n' "# service <service_name> onedisable"

    printf '\n%s\n' "** REVIEW ** Required: Manually review the output above against your site policy."
}        
BASH        
    ],
];
