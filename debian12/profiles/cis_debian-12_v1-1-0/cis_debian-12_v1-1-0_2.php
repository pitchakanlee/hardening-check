<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 2
// =============================================================
return [
    // --- 2 Services ---
    [ 'id' => '2', 'title' => 'Services', 'type' => 'header' ],

    // --- 2.1 Configure Server Services  ---
    [ 'id' => '2.1', 'title' => 'Configure Server Services', 'type' => 'header' ],

    // --- 2.1.1 Ensure autofs services are not in use ---
    [
        'id' => '2.1.1', 'title' => 'Ensure autofs services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s autofs &>/dev/null; then
        a_output_pass+=("  - Package 'autofs' is not installed.")
    else
        a_output_info+=("  - Package 'autofs' is installed, checking service status...")

        if systemctl is-enabled autofs.service 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - 'autofs.service' is enabled.")
        else
            a_output_pass+=("  - 'autofs.service' is not enabled.")
        fi

        if systemctl is-active autofs.service 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - 'autofs.service' is active.")
        else
            a_output_pass+=("  - 'autofs.service' is not active.")
        fi

        a_output_info+=("  - Note: If 'autofs' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If 'autofs' is not required, run '# sudo apt purge autofs'."
        printf '%s\n' "If 'autofs' is required, run '# sudo systemctl --now disable autofs.service' to stop and disable it."
    fi
}
BASH
    ],

    // --- 2.1.2 Ensure avahi daemon services are not in use ---
    [
        'id' => '2.1.2', 'title' => 'Ensure avahi daemon services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s avahi-daemon &>/dev/null; then
        a_output_pass+=("  - Package 'avahi-daemon' is not installed.")
    else
        a_output_info+=("  - Package 'avahi-daemon' is installed, checking service and socket status...")

        if systemctl is-enabled avahi-daemon.service 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - 'avahi-daemon.service' is enabled.")
        else
            a_output_pass+=("  - 'avahi-daemon.service' is not enabled.")
        fi
        
        if systemctl is-enabled avahi-daemon.socket 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - 'avahi-daemon.socket' is enabled.")
        else
            a_output_pass+=("  - 'avahi-daemon.socket' is not enabled.")
        fi

        if systemctl is-active avahi-daemon.service 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - 'avahi-daemon.service' is active.")
        else
            a_output_pass+=("  - 'avahi-daemon.service' is not active.")
        fi

        if systemctl is-active avahi-daemon.socket 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - 'avahi-daemon.socket' is active.")
        else
            a_output_pass+=("  - 'avahi-daemon.socket' is not active.")
        fi

        a_output_info+=("  - Note: If 'avahi-daemon' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Remediation --"
        printf '%s\n' "If 'avahi-daemon' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop avahi-daemon.socket avahi-daemon.service"
        printf '%s\n' "# sudo apt purge avahi-daemon"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop avahi-daemon.socket avahi-daemon.service"
        printf '%s\n' "# sudo systemctl mask avahi-daemon.socket avahi-daemon.service"
    fi
}
BASH
    ],

    // --- 2.1.3 Ensure dhcp server services are not in use ---
    [
        'id' => '2.1.3', 'title' => 'Ensure dhcp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s isc-dhcp-server &>/dev/null; then
        a_output_pass+=("  - Package 'isc-dhcp-server' is not installed.")
    else
        a_output_info+=("  - Package 'isc-dhcp-server' is installed, checking service status...")
        services_to_check=("isc-dhcp-server.service" "isc-dhcp-server6.service")

        for service in "${services_to_check[@]}"; do
            if systemctl is-enabled "$service" 2>/dev/null | grep -q 'enabled'; then
                a_output_fail+=("  - '$service' is enabled.")
            else
                a_output_pass+=("  - '$service' is not enabled.")
            fi

            if systemctl is-active "$service" 2>/dev/null | grep -q 'active'; then
                a_output_fail+=("  - '$service' is active.")
            else
                a_output_pass+=("  - '$service' is not active.")
            fi
        done

        a_output_info+=("  - Note: If 'isc-dhcp-server' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If 'isc-dhcp-server' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop isc-dhcp-server.service isc-dhcp-server6.service"
        printf '%s\n' "# sudo apt purge isc-dhcp-server"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop isc-dhcp-server.service isc-dhcp-server6.service"
        printf '%s\n' "# sudo systemctl mask isc-dhcp-server.service isc-dhcp-server6.service"
    fi
}
BASH
    ],

    // --- 2.1.4 Ensure dns server services are not in use ---
    [
        'id' => '2.1.4', 'title' => 'Ensure dns server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s bind9 &>/dev/null; then
        a_output_pass+=("  - Package 'bind9' is not installed.")
    else
        a_output_info+=("  - Package 'bind9' is installed, checking service status...")
        SERVICE_NAME="named.service"

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If 'bind9' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If 'bind9' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop named.service"
        printf '%s\n' "# sudo apt purge bind9"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop named.service"
        printf '%s\n' "# sudo systemctl mask named.service"
    fi
}
BASH
    ],

    // --- 2.1.5 Ensure dnsmasq services are not in use ---
    [
        'id' => '2.1.5', 'title' => 'Ensure dnsmasq services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="dnsmasq.service"
    PACKAGE_NAME="dnsmasq"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.6 Ensure ftp server services are not in use ---
    [
        'id' => '2.1.6', 'title' => 'Ensure ftp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="vsftpd.service"
    PACKAGE_NAME="vsftpd"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.7 Ensure ldap server services are not in use ---
    [
        'id' => '2.1.7', 'title' => 'Ensure ldap server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="slapd.service"
    PACKAGE_NAME="slapd"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.8 Ensure message access server services are not in use ---
    [
        'id' => '2.1.8', 'title' => 'Ensure message access server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    
    package_imapd_installed=$(dpkg-query -s dovecot-imapd &>/dev/null && echo "true" || echo "false")
    package_pop3d_installed=$(dpkg-query -s dovecot-pop3d &>/dev/null && echo "true" || echo "false")

    if [ "$package_imapd_installed" == "false" ] && [ "$package_pop3d_installed" == "false" ]; then
        a_output_pass+=("  - Packages 'dovecot-imapd' and 'dovecot-pop3d' are not installed.")
    else
        a_output_info+=("  - Dovecot package(s) are installed, checking service status...")

        if [ "$package_imapd_installed" == "true" ]; then
            a_output_info+=("  - 'dovecot-imapd' is installed.")
        fi
        if [ "$package_pop3d_installed" == "true" ]; then
            a_output_info+=("  - 'dovecot-pop3d' is installed.")
        fi

        services_to_check=("dovecot.service" "dovecot.socket")
        for service in "${services_to_check[@]}"; do
            if systemctl is-enabled "$service" 2>/dev/null | grep -q 'enabled'; then
                a_output_fail+=("  - '$service' is enabled.")
            else
                a_output_pass+=("  - '$service' is not enabled.")
            fi

            if systemctl is-active "$service" 2>/dev/null | grep -q 'active'; then
                a_output_fail+=("  - '$service' is active.")
            else
                a_output_pass+=("  - '$service' is not active.")
            fi
        done

        a_output_info+=("  - Note: If a Dovecot package is required, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If Dovecot packages are not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop dovecot.socket dovecot.service"
        printf '%s\n' "# sudo apt purge dovecot-imapd dovecot-pop3d"
        printf '%s\n' ""
        printf '%s\n' "- OR - If a package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop dovecot.socket dovecot.service"
        printf '%s\n' "# sudo systemctl mask dovecot.socket dovecot.service"
    fi
}
BASH
    ],

    // --- 2.1.9 Ensure network file system services are not in use ---
    [
        'id' => '2.1.9', 'title' => 'Ensure network file system services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="nfs-server.service"
    PACKAGE_NAME="nfs-kernel-server"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.10 Ensure nis server services are not in use ---
    [
        'id' => '2.1.10', 'title' => 'Ensure nis server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="ypserv.service"
    PACKAGE_NAME="ypserv"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.11 Ensure print server services are not in use ---
    [
        'id' => '2.1.11', 'title' => 'Ensure print server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PACKAGE_NAME="cups"
    SERVICES_TO_CHECK=("cups.socket" "cups.service")

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service and socket status...")

        for service in "${SERVICES_TO_CHECK[@]}"; do
            if systemctl is-enabled "$service" 2>/dev/null | grep -q 'enabled'; then
                a_output_fail+=("  - '$service' is enabled.")
            else
                a_output_pass+=("  - '$service' is not enabled.")
            fi

            if systemctl is-active "$service" 2>/dev/null | grep -q 'active'; then
                a_output_fail+=("  - '$service' is active.")
            else
                a_output_pass+=("  - '$service' is not active.")
            fi
        done

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop cups.socket cups.service"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop cups.socket cups.service"
        printf '%s\n' "# sudo systemctl mask cups.socket cups.service"
    fi
}
BASH
    ],

    // --- 2.1.12 Ensure rpcbind services are not in use ---
    [
        'id' => '2.1.12', 'title' => 'Ensure rpcbind services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    PACKAGE_NAME="rpcbind"
    SERVICES_TO_CHECK=("rpcbind.socket" "rpcbind.service")

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service and socket status...")

        for service in "${SERVICES_TO_CHECK[@]}"; do
            if systemctl is-enabled "$service" 2>/dev/null | grep -q 'enabled'; then
                a_output_fail+=("  - '$service' is enabled.")
            else
                a_output_pass+=("  - '$service' is not enabled.")
            fi

            if systemctl is-active "$service" 2>/dev/null | grep -q 'active'; then
                a_output_fail+=("  - '$service' is active.")
            else
                a_output_pass+=("  - '$service' is not active.")
            fi
        done

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop cups.socket cups.service"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop cups.socket cups.service"
        printf '%s\n' "# sudo systemctl mask cups.socket cups.service"
    fi
}
BASH
    ],

    // --- 2.1.13 Ensure rsync services are not in use ---
    [
        'id' => '2.1.13', 'title' => 'Ensure rsync services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="rsync.service"
    PACKAGE_NAME="rsync"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.14 Ensure samba file server services are not in use ---
    [
        'id' => '2.1.14', 'title' => 'Ensure samba file server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="samba.service"
    PACKAGE_NAME="smbd"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.15 Ensure snmp services are not in use ---
    [
        'id' => '2.1.15', 'title' => 'Ensure snmp services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="snmpd.service"
    PACKAGE_NAME="snmpd"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.16 Ensure tftp server services are not in use ---
    [
        'id' => '2.1.16', 'title' => 'Ensure tftp server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="tftpd-hpa.service"
    PACKAGE_NAME="tftpd-hpa"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}

BASH
    ],

    // --- 2.1.17 Ensure web proxy server services are not in use ---
    [
        'id' => '2.1.17', 'title' => 'Ensure web proxy server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="squid.service"
    PACKAGE_NAME="squid"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}

BASH
    ],

    // --- 2.1.18 Ensure web server services are not in use ---
    [
        'id' => '2.1.18', 'title' => 'Ensure web server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="apache2.service"
    PACKAGE_NAME="apache2"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.19 Ensure xinetd services are not in use ---
    [
        'id' => '2.1.19', 'title' => 'Ensure xinetd services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="xinetd.service"
    PACKAGE_NAME="xinetd"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_info+=("  - Package '$PACKAGE_NAME' is installed, checking service status...")

        if systemctl is-enabled "$SERVICE_NAME" 2>/dev/null | grep -q 'enabled'; then
            a_output_fail+=("  - '$SERVICE_NAME' is enabled.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not enabled.")
        fi

        if systemctl is-active "$SERVICE_NAME" 2>/dev/null | grep -q 'active'; then
            a_output_fail+=("  - '$SERVICE_NAME' is active.")
        else
            a_output_pass+=("  - '$SERVICE_NAME' is not active.")
        fi

        a_output_info+=("  - Note: If '$PACKAGE_NAME' is required by another package, ensure this is compliant with your site policy.")
        a_output_info+=("  - Note: Other FTP server packages may exist and should also be audited.")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If '$PACKAGE_NAME' is not required, run the following commands:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If the package is required as a dependency, run:"
        printf '%s\n' "# sudo systemctl stop $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.20 Ensure X window server services are not in use ---
    [
        'id' => '2.1.20', 'title' => 'Ensure X window server services are not in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGE_NAME="xserver-common"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_fail+=("  - Package '$PACKAGE_NAME' is installed.")
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If a Graphical Desktop Manager or X-Windows server is not required,"
        printf '%s\n' "run the following command to remove the package:"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
    fi
}
BASH
    ],

    // --- 2.1.21 Ensure mail transfer agent is configured for local-only mode ---
    [
        'id' => '2.1.21', 'title' => 'Ensure mail transfer agent is configured for local-only mode', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
   a_output=()
   a_output2=()
   a_port_list=("25" "465" "587")

   for l_port_number in "${a_port_list[@]}"; do
      if ss -plntu | grep -P -- ":$l_port_number\b" | grep -Pvq '\h+(127\.0\.0\.1|\[?::1\]?):'"$l_port_number"'\b'; then
         a_output2+=(" - Port \"$l_port_number\" is listening on a non-loopback network interface")
      else
         a_output+=(" - Port \"$l_port_number\" is not listening on a non-loopback network interface")
      fi
   done

   l_interfaces=""
   if command -v postconf &> /dev/null; then
      l_interfaces="$(postconf -n inet_interfaces 2>/dev/null | awk '{print $2}')"
   elif command -v exim &> /dev/null; then
      l_interfaces="$(exim -bP local_interfaces 2>/dev/null | awk '{print $2}')"
   elif command -v sendmail &> /dev/null; then
      l_interfaces="$(grep -i 'O DaemonPortOptions=' /etc/mail/sendmail.cf | grep -oP '(?<=Addr=)[^,]+')"
   fi

   if [ -n "$l_interfaces" ]; then
      if grep -Pqi '\ball\b' <<< "$l_interfaces"; then
         a_output2+=(" - MTA is bound to all network interfaces")
      elif ! grep -Pqi '(0\.0\.0\.0|::|::1|loopback-only)' <<< "$l_interfaces"; then
         a_output2+=(" - MTA is bound to a network interface" "   \"$l_interfaces\"")
      else
         a_output+=(" - MTA is not bound to a non-loopback network interface" "   \"$l_interfaces\"")
      fi
   else
      a_output+=(" - MTA not detected or in use")
   fi

   echo ""
   echo "- Audit Result:"
   if [ "${#a_output2[@]}" -eq 0 ]; then
      echo "  ** PASS **"
      printf '%s\n' "${a_output[@]}"
   else
      echo "  ** FAIL **"
      echo "  * Reasons for audit failure *"
      printf '%s\n' "${a_output2[@]}"
      if [ "${#a_output[@]}" -gt 0 ]; then
         echo ""
         echo "- Correctly set:"
         printf '%s\n' "${a_output[@]}"
      fi
   fi
}
BASH
    ],

    // --- 2.1.22 Ensure only approved services are listening on a network interface ---
    [
        'id' => '2.1.22', 'title' => 'Ensure only approved services are listening on a network interface', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "Listing all listening services and ports."
    printf '%s\n' "Please review the following output to ensure all services are required and approved by your site policy."
    printf '%s\n' "------------------------------------------------------------------------------------------------"

    ss -plntu

    printf '%s\n' "------------------------------------------------------------------------------------------------"

    printf '\n\n%s\n' "-- Suggestion / Remediation --"
    printf '%s\n' "For any unapproved service listed above:"
    printf '%s\n' "1. Identify the package that provides the service."
    printf '%s\n' "2. If the package is not required, remove it:"
    printf '%s\n' "   # sudo systemctl stop <service_name>.socket <service_name>.service"
    printf '%s\n' "   # sudo apt purge <package_name>"
    printf '%s\n' ""
    printf '%s\n' "3. If the package is required as a dependency, stop and mask the service:"
    printf '%s\n' "   # sudo systemctl stop <service_name>.socket <service_name>.service"
    printf '%s\n' "   # sudo systemctl mask <service_name>.socket <service_name>.service"

    printf '\n%s\n' "** REVIEW ** Required: Manually review the output above against your site policy."
}
BASH
    ],

    // --- 2.2 Configure Client Services ---
    [ 'id' => '2.2', 'title' => 'Configure Client Services', 'type' => 'header' ],

    // --- 2.2.1 Ensure NIS Client is not installed ---
    [
        'id' => '2.2.1', 'title' => 'Ensure NIS Client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGE_NAME="nis"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_fail+=("  - Package '$PACKAGE_NAME' is installed.")
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following command to remove the LDAP client package:"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
    fi
}
BASH
    ],

    // --- 2.2.2 Ensure rsh client is not installed ---
    [
        'id' => '2.2.2', 'title' => 'Ensure rsh client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGE_NAME="rsh-client"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_fail+=("  - Package '$PACKAGE_NAME' is installed.")
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following command to remove the LDAP client package:"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
    fi
}
BASH
    ],

    // --- 2.2.3 Ensure talk client is not installed ---
    [
        'id' => '2.2.3', 'title' => 'Ensure talk client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGE_NAME="talk"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_fail+=("  - Package '$PACKAGE_NAME' is installed.")
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following command to remove the LDAP client package:"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
    fi
}
BASH
    ],
    // --- 2.2.4 Ensure telnet client is not installed ---
    [
        'id' => '2.2.4', 'title' => 'Ensure telnet client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGES_TO_CHECK=("telnet" "inetutils-telnet")

    for package in "${PACKAGES_TO_CHECK[@]}"; do
        if ! dpkg-query -s "$package" &>/dev/null; then
            a_output_pass+=("  - Package '$package' is not installed.")
        else
            a_output_fail+=("  - Package '$package' is installed.")
        fi
    done

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to remove the telnet client packages:"
        printf '%s\n' "# sudo apt purge telnet"
        printf '%s\n' "# sudo apt purge inetutils-telnet"
    fi
}

BASH
    ],

    // --- 2.2.5 Ensure ldap client is not installed ---
    [
        'id' => '2.2.5', 'title' => 'Ensure ldap client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGE_NAME="ldap-utils"

    if ! dpkg-query -s "$PACKAGE_NAME" &>/dev/null; then
        a_output_pass+=("  - Package '$PACKAGE_NAME' is not installed.")
    else
        a_output_fail+=("  - Package '$PACKAGE_NAME' is installed.")
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following command to remove the LDAP client package:"
        printf '%s\n' "# sudo apt purge $PACKAGE_NAME"
    fi
}
BASH
    ],

    // --- 2.2.6 Ensure ftp client is not installed ---
    [
        'id' => '2.2.6', 'title' => 'Ensure ftp client is not installed', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    PACKAGES_TO_CHECK=("ftp" "tnftp")

    for package in "${PACKAGES_TO_CHECK[@]}"; do
        if ! dpkg-query -s "$package" &>/dev/null; then
            a_output_pass+=("  - Package '$package' is not installed.")
        else
            a_output_fail+=("  - Package '$package' is installed.")
        fi
    done

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to remove the telnet client packages:"
        printf '%s\n' "# sudo apt purge telnet"
        printf '%s\n' "# sudo apt purge inetutils-telnet"
    fi
}
BASH
    ],

    // --- 2.3 Configure Time Synchronization ---
    [ 'id' => '2.3', 'title' => 'Configure Time Synchronization', 'type' => 'header' ],

    // --- 2.3.1 Ensure time synchronization is in use  ---
    [ 'id' => '2.3.1', 'title' => 'Ensure time synchronization is in use', 'type' => 'header' ],

    // --- 2.3.1.1 Ensure a single time synchronization daemon is in use ---
    [
        'id' => '2.3.1.1', 'title' => 'Ensure a single time synchronization daemon is in use', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash

{
    l_output="" l_output2=""

    # Function to check if a service is enabled or active
    service_not_enabled_chk() {
        local service_name="$1"
        local out=""

        if systemctl is-enabled "$service_name" 2>/dev/null | grep -q 'enabled'; then
            out+="\n  - Daemon: \"$service_name\" is enabled on the system"
        fi

        if systemctl is-active "$service_name" 2>/dev/null | grep -q '^active'; then
            out+="\n  - Daemon: \"$service_name\" is active on the system"
        fi

        echo -e "$out"
    }

    # Check systemd-timesyncd
    l_out_tsd=$(service_not_enabled_chk "systemd-timesyncd.service")
    if [ -n "$l_out_tsd" ]; then
        l_timesyncd="y"
    else
        l_timesyncd="n"
        l_out_tsd="\n  - Daemon: \"systemd-timesyncd.service\" is not enabled and not active on the system"
    fi

    # Check chrony
    l_out_chrony=$(service_not_enabled_chk "chrony.service")
    if [ -n "$l_out_chrony" ]; then
        l_chrony="y"
    else
        l_chrony="n"
        l_out_chrony="\n  - Daemon: \"chrony.service\" is not enabled and not active on the system"
    fi

    l_status="${l_timesyncd}${l_chrony}"

    case "$l_status" in
        yy)
            l_output2=" - More than one time sync daemon is in use on the system$l_out_tsd$l_out_chrony"
            ;;
        nn)
            l_output2=" - No time sync daemon is in use on the system$l_out_tsd$l_out_chrony"
            ;;
        yn|ny)
            l_output=" - Only one time sync daemon is in use on the system$l_out_tsd$l_out_chrony"
            ;;
        *)
            l_output2=" - Unable to determine time sync daemon(s) status"
            ;;
    esac

    if [ -z "$l_output2" ]; then
        echo -e "\n- Audit Result:\n  ** PASS **\n$l_output\n"
    else
        echo -e "\n- Audit Result:\n  ** FAIL **\n - * Reasons for audit failure *:\n$l_output2\n"
    fi
}
BASH
    ],

    // --- 2.3.2 Configure systemd-timesyncd ---
    [ 'id' => '2.3.2', 'title' => 'Configure systemd-timesyncd', 'type' => 'header' ],

    // --- 2.3.2.1 Ensure systemd-timesyncd configured with authorized timeserver ---
    [
        'id' => '2.3.2.1', 'title' => 'Ensure systemd-timesyncd configured with authorized timeserver', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    timesyncd_enabled=$(systemctl is-enabled systemd-timesyncd.service 2>/dev/null)
    timesyncd_active=$(systemctl is-active systemd-timesyncd.service 2>/dev/null)
    chrony_enabled=$(systemctl is-enabled chrony.service 2>/dev/null)
    chrony_active=$(systemctl is-active chrony.service 2>/dev/null)

    if [[ "$timesyncd_active" == "active" && "$chrony_active" == "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Both systemd-timesyncd and chrony are active."
        printf '%s\n' "  - Only one of the two time synchronization daemons should be in use."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" != "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Neither systemd-timesyncd nor chrony is active."
        printf '%s\n' "  - One of the two time synchronization daemons should be available."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" == "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'chrony.service' is the active time sync daemon."
        printf '%s\n' "  - This check for systemd-timesyncd configuration is not applicable."

    elif [[ "$timesyncd_active" == "active" && "$chrony_active" != "active" ]]; then
        a_output=(); a_output2=(); a_output3=(); a_out=(); a_out2=()
        a_parlist=("NTP=[^#\n\r]+" "FallbackNTP=[^#\n\r]+")
        l_systemd_config_file="/etc/systemd/timesyncd.conf"

        f_config_file_parameter_chk() {
            l_used_parameter_setting=""
            config_files_to_check=$(systemd-analyze cat-config "$l_systemd_config_file" | grep -Pio '^\s*#\s*/[^#\n\r\s]+\.conf\b' | tac)

            for l_file in $config_files_to_check; do
                l_file="$(tr -d '# ' <<< "$l_file")"
                l_used_parameter_setting="$(grep -PHs -- "^\s*${l_parameter_name}\b" "$l_file" | tail -n 1)"
                [ -n "$l_used_parameter_setting" ] && break
            done

            if [ -n "$l_used_parameter_setting" ]; then
                l_file_name=$(echo "$l_used_parameter_setting" | cut -d: -f1)
                l_file_parameter=$(echo "$l_used_parameter_setting" | cut -d: -f2-)
                l_file_parameter_name=$(echo "$l_file_parameter" | awk -F= '{print $1}' | xargs)
                l_file_parameter_value=$(echo "$l_file_parameter" | awk -F= '{print $2}' | xargs)

                if grep -Pq -- "$l_parameter_value" <<< "$l_file_parameter_value"; then
                    a_out+=("  - Parameter: \"$l_file_parameter_name\" is set in file: \"$l_file_name\"")
                    a_out+=("    Value: \"$l_file_parameter_value\"")
                else
                     a_out2+=("  - Parameter: \"$l_file_parameter_name\" is incorrectly set in file: \"$l_file_name\"")
                fi
            else
                a_out2+=("  - Parameter: \"$l_parameter_name\" is not set in any configuration file.")
            fi
        }

        while IFS="=" read -r l_parameter_name l_parameter_value; do
            f_config_file_parameter_chk
        done < <(printf '%s\n' "${a_parlist[@]}")

        if [ "${#a_out[@]}" -gt 0 ]; then
            a_output+=("${a_out[@]}")
        fi
        if [ "${#a_out2[@]}" -gt 0 ]; then
             a_output2+=("${a_out2[@]}")
        fi

        if [ "${#a_output2[@]}" -le 0 ]; then
            printf '%s\n' "" "- Audit Result:" "  ** PASS **"
            printf '%s\n' "${a_output[@]}"
        else
            printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "${a_output2[@]}"
        fi
    fi
}

BASH
    ],

    // --- 2.3.2.2 Ensure systemd-timesyncd is enabled and running ---
    [
        'id' => '2.3.2.2', 'title' => 'Ensure systemd-timesyncd is enabled and running', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="systemd-timesyncd.service"

    a_output_info+=("  - Auditing status for '$SERVICE_NAME'.")

    enable_status=$(systemctl is-enabled "$SERVICE_NAME" 2>/dev/null)
    if [ "$enable_status" == "enabled" ]; then
        a_output_pass+=("  - '$SERVICE_NAME' is enabled.")
    else
        a_output_fail+=("  - '$SERVICE_NAME' is not enabled (status: '$enable_status').")
    fi

    active_status=$(systemctl is-active "$SERVICE_NAME" 2>/dev/null)
    if [ "$active_status" == "active" ]; then
        a_output_pass+=("  - '$SERVICE_NAME' is active.")
    else
        a_output_fail+=("  - '$SERVICE_NAME' is not active (status: '$active_status').")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If 'systemd-timesyncd' is your chosen time sync daemon, run:"
        printf '%s\n' "# sudo systemctl unmask $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl --now enable $SERVICE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If another time synchronization service is in use, run:"
        printf '%s\n' "# sudo systemctl --now mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.3.3 Configure chrony ---
    [ 'id' => '2.3.3', 'title' => 'Configure chrony', 'type' => 'header' ],

    // --- 2.3.3.1 Ensure chrony is configured with authorized timeserver ---
    [
        'id' => '2.3.3.1', 'title' => 'Ensure chrony is configured with authorized timeserver', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    timesyncd_enabled=$(systemctl is-enabled systemd-timesyncd.service 2>/dev/null)
    timesyncd_active=$(systemctl is-active systemd-timesyncd.service 2>/dev/null)
    chrony_enabled=$(systemctl is-enabled chrony.service 2>/dev/null)
    chrony_active=$(systemctl is-active chrony.service 2>/dev/null)

    if [[ "$timesyncd_active" == "active" && "$chrony_active" == "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Both systemd-timesyncd and chrony are active."
        printf '%s\n' "  - Only one of the two time synchronization daemons should be in use."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" != "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Neither systemd-timesyncd nor chrony is active."
        printf '%s\n' "  - One of the two time synchronization daemons should be available."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" == "active" ]]; then
        if [ ! -f /etc/chrony/chrony.conf ]; then
            printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "  - chrony is active but /etc/chrony/chrony.conf does not exist."
            exit 1
        fi

        a_output=()
        a_output2=()
        a_config_files=("/etc/chrony/chrony.conf")

        l_include='(confdir|sourcedir)'
        l_parameter_name='(server|pool)'
        l_parameter_value='.+'

        while IFS= read -r l_conf_loc; do
            l_dir=""
            l_ext=""

            if [ -d "$l_conf_loc" ]; then
                l_dir="$l_conf_loc"
                l_ext="*"
            elif grep -Psq '/\*\.([^#/\n\r]+)?\s*$' <<< "$l_conf_loc" || [ -f "$(readlink -f "$l_conf_loc")" ]; then
                l_dir="$(dirname "$l_conf_loc")"
                l_ext="$(basename "$l_conf_loc")"
            fi

            if [[ -n "$l_dir" && -n "$l_ext" ]]; then
                while IFS= read -r -d $'\0' l_file_name; do
                    if [ -f "$(readlink -f "$l_file_name")" ]; then
                        a_config_files+=("$(readlink -f "$l_file_name")")
                    fi
                done < <(find -L "$l_dir" -type f -name "$l_ext" -print0 2>/dev/null)
            fi
        done < <(awk '$1 ~ /^\s*'"$l_include"'$/ {print $2}' "${a_config_files[@]}" 2>/dev/null)

        for l_file in "${a_config_files[@]}"; do
            l_parameter_line=$(grep -Psi "^\s*${l_parameter_name}(\s+|\s*:\s*)${l_parameter_value}\b" "$l_file" 2>/dev/null)
            if [ -n "$l_parameter_line" ]; then
                a_output+=("  - Parameter: \"$(tr -d '()' <<< "${l_parameter_name//|/ or }")\""
                        "    Exists in the file: \"$l_file\" as:"
                        "$l_parameter_line")
            fi
        done

        if [ "${#a_output[@]}" -eq 0 ]; then
            a_output2+=("  - Parameter: \"$(tr -d '()' <<< "${l_parameter_name//|/ or }")\""
                    "    Does not exist in the chrony configuration")
        fi

        if [ "${#a_output2[@]}" -eq 0 ]; then
            printf '%s\n' "" "- Audit Result:" "  ** PASS **" "${a_output[@]}" ""
        else
            printf '%s\n' "" "- Audit Result:" "  ** FAIL **" " - Reason(s) for audit failure:" "${a_output2[@]}"
        fi

        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - chrony is active and configuration file exists."

    elif [[ "$timesyncd_active" == "active" && "$chrony_active" != "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'systemd-timesyncd.service' is the active time sync daemon."
        printf '%s\n' "  - This check for chrony configuration is not applicable."
    fi
}

BASH
    ],

    // --- 2.3.3.2 Ensure chrony is running as user _chrony ---
    [
        'id' => '2.3.3.2', 'title' => 'Ensure chrony is running as user _chrony', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    timesyncd_active=$(systemctl is-active systemd-timesyncd.service 2>/dev/null)
    chrony_active=$(systemctl is-active chrony.service 2>/dev/null)

    if [[ "$timesyncd_active" == "active" && "$chrony_active" == "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Both systemd-timesyncd and chrony are active."
        printf '%s\n' "  - Only one of the two time synchronization daemons should be in use."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" != "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Neither systemd-timesyncd nor chrony is active."
        printf '%s\n' "  - One of the two time synchronization daemons should be available."

    elif [[ "$timesyncd_active" != "active" && "$chrony_active" == "active" ]]; then
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        a_output_info+=("  - 'chrony.service' is active, checking the process user.")
        offending_user=$(ps -ef | awk '(/[c]hronyd/ && $1!="_chrony") { print $1 }')

        if [ -z "$offending_user" ]; then
            a_output_pass+=("  - The chronyd process is running as the correct user ('_chrony').")
        else
            a_output_fail+=("  - The chronyd process is running as user '$offending_user' instead of '_chrony'.")
        fi

        if [ "${#a_output_info[@]}" -gt 0 ]; then
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "${a_output_info[@]}"
        fi

        if [ "${#a_output_fail[@]}" -le 0 ]; then
            printf '\n%s\n' "- Audit Result:" "  ** PASS **"
            printf '%s\n' "${a_output_pass[@]}"
        else
            printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "${a_output_fail[@]}"

            if [ "${#a_output_pass[@]}" -gt 0 ]; then
                printf '\n%s\n' "- Correctly set:"
                printf '%s\n' "${a_output_pass[@]}"
            fi

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Add or edit the following line in /etc/chrony/chrony.conf or a related config file:"
            printf '%s\n' "user _chrony"
            printf '%s\n' ""
            printf '%s\n' "- OR - If another time synchronization service is in use, remove chrony:"
            printf '%s\n' "# sudo apt purge chrony"
        fi

    elif [[ "$timesyncd_active" == "active" && "$chrony_active" != "active" ]]; then
        printf '%s\n' "" "- Audit Result:" "  ** REVIEW **"
        printf '%s\n' "  - SKIPPED: 'systemd-timesyncd.service' is the active time sync daemon."
        printf '%s\n' "  - This check for chrony configuration is not applicable."
    fi
}
BASH
    ],

    // --- 2.3.3.3 Ensure chrony is enabled and running ---
    [
        'id' => '2.3.3.3', 'title' => 'Ensure chrony is enabled and running', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="chrony.service"

    a_output_info+=("  - Auditing status for '$SERVICE_NAME'.")

    enable_status=$(systemctl is-enabled "$SERVICE_NAME" 2>/dev/null)
    if [ "$enable_status" == "enabled" ]; then
        a_output_pass+=("  - '$SERVICE_NAME' is enabled.")
    else
        a_output_fail+=("  - '$SERVICE_NAME' is not enabled (status: '$enable_status').")
    fi

    active_status=$(systemctl is-active "$SERVICE_NAME" 2>/dev/null)
    if [ "$active_status" == "active" ]; then
        a_output_pass+=("  - '$SERVICE_NAME' is active.")
    else
        a_output_fail+=("  - '$SERVICE_NAME' is not active (status: '$active_status').")
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If 'systemd-timesyncd' is your chosen time sync daemon, run:"
        printf '%s\n' "# sudo systemctl unmask $SERVICE_NAME"
        printf '%s\n' "# sudo systemctl --now enable $SERVICE_NAME"
        printf '%s\n' ""
        printf '%s\n' "- OR - If another time synchronization service is in use, run:"
        printf '%s\n' "# sudo systemctl --now mask $SERVICE_NAME"
    fi
}
BASH
    ],

    // --- 2.4 Job Schedulers ---
    [ 'id' => '2.4', 'title' => 'Job Schedulers', 'type' => 'header' ],

    // --- 2.4.1 Configure cron ---
    [ 'id' => '2.4.1', 'title' => 'Configure cron', 'type' => 'header' ],

    // --- 2.4.1.1 Ensure cron daemon is enabled and active ---
    [
        'id' => '2.4.1.1', 'title' => 'Ensure cron daemon is enabled and active', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s cron &>/dev/null && ! dpkg-query -s anacron &>/dev/null; then
        a_output_pass+=("  - No cron daemon (cron, anacron) is installed on the system, this check is not applicable.")
    else
        a_output_info+=("  - A cron daemon is installed, checking service status...")

        CRON_SERVICE=$(systemctl list-unit-files | awk '$1~/^crond?\.service/{print $1}')

        if [ -z "$CRON_SERVICE" ]; then
            a_output_fail+=("  - Could not determine the cron service name (cron.service or crond.service).")
        else
            a_output_info+=("  - Identified cron service as: '$CRON_SERVICE'")

            enable_status=$(systemctl is-enabled "$CRON_SERVICE" 2>/dev/null)
            if [ "$enable_status" == "enabled" ]; then
                a_output_pass+=("  - '$CRON_SERVICE' is enabled.")
            else
                a_output_fail+=("  - '$CRON_SERVICE' is not enabled (status: '$enable_status').")
            fi

            active_status=$(systemctl is-active "$CRON_SERVICE" 2>/dev/null)
            if [ "$active_status" == "active" ]; then
                a_output_pass+=("  - '$CRON_SERVICE' is active.")
            else
                a_output_fail+=("  - '$CRON_SERVICE' is not active (status: '$active_status').")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "If cron is required, run the following commands to enable and start it:"
        printf '%s\n' "# sudo systemctl unmask \$(systemctl list-unit-files | awk '\$1~/^crond?\.service/{print \$1}')"
        printf '%s\n' "# sudo systemctl --now enable \$(systemctl list-unit-files | awk '\$1~/^crond?\.service/{print \$1}')"
    fi
}
BASH
    ],

    // --- 2.4.1.2 Ensure permissions on /etc/crontab are configured ---
    [
        'id' => '2.4.1.2', 'title' => 'Ensure permissions on /etc/crontab are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_FILE="/etc/crontab"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with file check.")

        if [ ! -f "$TARGET_FILE" ]; then
            a_output_fail+=("  - File '$TARGET_FILE' not found.")
        else
            a_output_info+=("  - Checking file: $TARGET_FILE")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_FILE")")

            PERMS=$(stat -Lc '%#a' "$TARGET_FILE")
            FILE_UID=$(stat -Lc '%u' "$TARGET_FILE")
            FILE_GID=$(stat -Lc '%g' "$TARGET_FILE")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 600 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 600 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/crontab"
        printf '%s\n' "# sudo chmod og-rwx /etc/crontab"
    fi
}
BASH
    ],

    // --- 2.4.1.3 Ensure permissions on /etc/cron.hourly are configured ---
    [
        'id' => '2.4.1.3', 'title' => 'Ensure permissions on /etc/cron.hourly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_DIR="/etc/cron.hourly"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with directory check.")

        if [ ! -d "$TARGET_DIR" ]; then
            a_output_fail+=("  - Directory '$TARGET_DIR' not found.")
        else
            a_output_info+=("  - Checking directory: $TARGET_DIR")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_DIR")")

            PERMS=$(stat -Lc '%#a' "$TARGET_DIR")
            FILE_UID=$(stat -Lc '%u' "$TARGET_DIR")
            FILE_GID=$(stat -Lc '%g' "$TARGET_DIR")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 700 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 700 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/cron.hourly"
        printf '%s\n' "# sudo chmod og-rwx /etc/cron.hourly"
    fi
}
BASH
    ],

    // --- 2.4.1.4 Ensure permissions on /etc/cron.daily are configured ---
    [
        'id' => '2.4.1.4', 'title' => 'Ensure permissions on /etc/cron.daily are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_DIR="/etc/cron.daily"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with directory check.")

        if [ ! -d "$TARGET_DIR" ]; then
            a_output_fail+=("  - Directory '$TARGET_DIR' not found.")
        else
            a_output_info+=("  - Checking directory: $TARGET_DIR")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_DIR")")

            PERMS=$(stat -Lc '%#a' "$TARGET_DIR")
            FILE_UID=$(stat -Lc '%u' "$TARGET_DIR")
            FILE_GID=$(stat -Lc '%g' "$TARGET_DIR")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 700 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 700 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/cron.daily"
        printf '%s\n' "# sudo chmod og-rwx /etc/cron.daily"
    fi
}
BASH
    ],

    // --- 2.4.1.5 Ensure permissions on /etc/cron.weekly are configured ---
    [
        'id' => '2.4.1.5', 'title' => 'Ensure permissions on /etc/cron.weekly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_DIR="/etc/cron.weekly"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with directory check.")

        if [ ! -d "$TARGET_DIR" ]; then
            a_output_fail+=("  - Directory '$TARGET_DIR' not found.")
        else
            a_output_info+=("  - Checking directory: $TARGET_DIR")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_DIR")")

            PERMS=$(stat -Lc '%#a' "$TARGET_DIR")
            FILE_UID=$(stat -Lc '%u' "$TARGET_DIR")
            FILE_GID=$(stat -Lc '%g' "$TARGET_DIR")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 700 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 700 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/cron.weekly"
        printf '%s\n' "# sudo chmod og-rwx /etc/cron.weekly"
    fi
}
BASH
    ],

    // --- 2.4.1.6 Ensure permissions on /etc/cron.monthly are configured ---
    [
        'id' => '2.4.1.6', 'title' => 'Ensure permissions on /etc/cron.monthly are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_DIR="/etc/cron.monthly"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with directory check.")

        if [ ! -d "$TARGET_DIR" ]; then
            a_output_fail+=("  - Directory '$TARGET_DIR' not found.")
        else
            a_output_info+=("  - Checking directory: $TARGET_DIR")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_DIR")")

            PERMS=$(stat -Lc '%#a' "$TARGET_DIR")
            FILE_UID=$(stat -Lc '%u' "$TARGET_DIR")
            FILE_GID=$(stat -Lc '%g' "$TARGET_DIR")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 700 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 700 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/cron.monthly"
        printf '%s\n' "# sudo chmod og-rwx /etc/cron.monthly"
    fi
}
BASH
    ],

    // --- 2.4.1.7 Ensure permissions on /etc/cron.d are configured ---
    [
        'id' => '2.4.1.7', 'title' => 'Ensure permissions on /etc/cron.d are configured', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    TARGET_DIR="/etc/cron.d"

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with directory check.")

        if [ ! -d "$TARGET_DIR" ]; then
            a_output_fail+=("  - Directory '$TARGET_DIR' not found.")
        else
            a_output_info+=("  - Checking directory: $TARGET_DIR")
            a_output_info+=("  - Actual state: $(stat -Lc 'Access: (%#a/%A) Uid: (%u/%U) Gid: (%g/%G)' "$TARGET_DIR")")

            PERMS=$(stat -Lc '%#a' "$TARGET_DIR")
            FILE_UID=$(stat -Lc '%u' "$TARGET_DIR")
            FILE_GID=$(stat -Lc '%g' "$TARGET_DIR")

            if [ $(( $PERMS & 0077 )) -eq 0 ]; then
                a_output_pass+=("  - Permissions ('$PERMS') are 700 or more restrictive.")
            else
                a_output_fail+=("  - Permissions ('$PERMS') are NOT 700 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - Owner is 'root' (UID 0).")
            else
                a_output_fail+=("  - Owner is not 'root' (UID is $FILE_UID).")
            fi

            if [ "$FILE_GID" -eq 0 ]; then
                a_output_pass+=("  - Group is 'root' (GID 0).")
            else
                a_output_fail+=("  - Group is not 'root' (GID is $FILE_GID).")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to set the correct ownership and permissions:"
        printf '%s\n' "# sudo chown root:root /etc/cron.d"
        printf '%s\n' "# sudo chmod og-rwx /etc/cron.d"
    fi
}
BASH
    ],

    // --- 2.4.1.8 Ensure crontab is restricted to authorized users ---
    [
        'id' => '2.4.1.8', 'title' => 'Ensure crontab is restricted to authorized users', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s cron &>/dev/null; then
        a_output_pass+=("  - Package 'cron' is not installed, this check is not applicable.")
    else
        a_output_info+=("  - Package 'cron' is installed, proceeding with file checks.")

        CRON_ALLOW="/etc/cron.allow"
        a_output_info+=("  - Auditing file: $CRON_ALLOW")

        if [ ! -f "$CRON_ALLOW" ]; then
            a_output_fail+=("  - File '$CRON_ALLOW' does not exist.")
        else
            PERMS=$(stat -Lc '%#a' "$CRON_ALLOW")
            FILE_UID=$(stat -Lc '%u' "$CRON_ALLOW")
            FILE_GID_NAME=$(stat -Lc '%G' "$CRON_ALLOW")

            a_output_info+=("  - Actual state for $CRON_ALLOW: $(stat -Lc 'Access: (%#a/%A) Owner: (%U) Group: (%G)' "$CRON_ALLOW")")

            if [ $(( $PERMS & 0037 )) -eq 0 ]; then
                a_output_pass+=("  - $CRON_ALLOW: Permissions ('$PERMS') are 640 or more restrictive.")
            else
                a_output_fail+=("  - $CRON_ALLOW: Permissions ('$PERMS') are NOT 640 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - $CRON_ALLOW: Owner is 'root'.")
            else
                a_output_fail+=("  - $CRON_ALLOW: Owner is not 'root'.")
            fi

            if [ "$FILE_GID_NAME" == "root" ] || [ "$FILE_GID_NAME" == "crontab" ]; then
                a_output_pass+=("  - $CRON_ALLOW: Group is '$FILE_GID_NAME', which is compliant.")
            else
                a_output_fail+=("  - $CRON_ALLOW: Group is '$FILE_GID_NAME', but should be 'root' or 'crontab'.")
            fi
        fi

        CRON_DENY="/etc/cron.deny"
        a_output_info+=("")
        a_output_info+=("  - Auditing file: $CRON_DENY")

        if [ ! -f "$CRON_DENY" ]; then
            a_output_pass+=("  - File '$CRON_DENY' does not exist, which is a compliant state.")
        else
            PERMS=$(stat -Lc '%#a' "$CRON_DENY")
            FILE_UID=$(stat -Lc '%u' "$CRON_DENY")
            FILE_GID_NAME=$(stat -Lc '%G' "$CRON_DENY")

            a_output_info+=("  - Actual state for $CRON_DENY: $(stat -Lc 'Access: (%#a/%A) Owner: (%U) Group: (%G)' "$CRON_DENY")")

            if [ $(( $PERMS & 0037 )) -eq 0 ]; then
                a_output_pass+=("  - $CRON_DENY: Permissions ('$PERMS') are 640 or more restrictive.")
            else
                a_output_fail+=("  - $CRON_DENY: Permissions ('$PERMS') are NOT 640 or more restrictive.")
            fi

            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - $CRON_DENY: Owner is 'root'.")
            else
                a_output_fail+=("  - $CRON_DENY: Owner is not 'root'.")
            fi

            if [ "$FILE_GID_NAME" == "root" ] || [ "$FILE_GID_NAME" == "crontab" ]; then
                a_output_pass+=("  - $CRON_DENY: Group is '$FILE_GID_NAME', which is compliant.")
            else
                a_output_fail+=("  - $CRON_DENY: Group is '$FILE_GID_NAME', but should be 'root' or 'crontab'.")
            fi
        fi
    fi

    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Run the following commands to correctly configure cron access control:"
        printf '%s\n' "# sudo rm /etc/cron.deny /etc/at.deny"
        printf '%s\n' "# sudo touch /etc/cron.allow /etc/at.allow"
        printf '%s\n' "# sudo chmod u-x,g-wx,o-rwx /etc/cron.allow /etc/at.allow"
        printf '%s\n' "# sudo chown root:root /etc/cron.allow /etc/at.allow"
    fi
}
BASH
    ],

    // --- 2.4.2 Configure at ---
    [ 'id' => '2.4.2', 'title' => 'Configure at', 'type' => 'header' ],
    //  if at is not installed on the system, this section can be skipped

    // 2.4.2.1 Ensure at is restricted to authorized users
    [
        'id' => '2.4.2.1', 'title' => 'Ensure at is restricted to authorized users', 'profile' => 'Level 2 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    # --- Part 1: Check if 'at' is installed ---
    if ! dpkg-query -s at &>/dev/null; then
        a_output_pass+=("  - Package 'at' is not installed, this check is not applicable.")
    else
        # --- Part 2: If 'at' is installed, proceed with file checks ---
        a_output_info+=("  - Package 'at' is installed, proceeding with access control file checks.")

        # --- Check /etc/at.allow ---
        AT_ALLOW="/etc/at.allow"
        a_output_info+=("")
        a_output_info+=("  - Auditing file: $AT_ALLOW")

        if [ ! -f "$AT_ALLOW" ]; then
            a_output_fail+=("  - File '$AT_ALLOW' does not exist, which is required.")
        else
            PERMS=$(stat -Lc '%#a' "$AT_ALLOW")
            FILE_UID=$(stat -Lc '%u' "$AT_ALLOW")
            FILE_GID_NAME=$(stat -Lc '%G' "$AT_ALLOW")

            a_output_info+=("  - Actual state for $AT_ALLOW: $(stat -Lc 'Access: (%#a/%A) Owner: (%U) Group: (%G)' "$AT_ALLOW")")

            # Check Permissions (640 or more restrictive)
            if [ $(( $PERMS & 0037 )) -eq 0 ]; then
                a_output_pass+=("  - $AT_ALLOW: Permissions ('$PERMS') are 640 or more restrictive.")
            else
                a_output_fail+=("  - $AT_ALLOW: Permissions ('$PERMS') are NOT 640 or more restrictive.")
            fi

            # Check Owner
            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - $AT_ALLOW: Owner is 'root'.")
            else
                a_output_fail+=("  - $AT_ALLOW: Owner is not 'root'.")
            fi

            # Check Group
            if [ "$FILE_GID_NAME" == "root" ] || [ "$FILE_GID_NAME" == "daemon" ]; then
                a_output_pass+=("  - $AT_ALLOW: Group is '$FILE_GID_NAME', which is compliant.")
            else
                a_output_fail+=("  - $AT_ALLOW: Group is '$FILE_GID_NAME', but should be 'root' or 'daemon'.")
            fi
        fi

        # --- Check /etc/at.deny ---
        AT_DENY="/etc/at.deny"
        a_output_info+=("")
        a_output_info+=("  - Auditing file: $AT_DENY")

        if [ ! -f "$AT_DENY" ]; then
            a_output_pass+=("  - File '$AT_DENY' does not exist, which is a compliant state.")
        else
            PERMS=$(stat -Lc '%#a' "$AT_DENY")
            FILE_UID=$(stat -Lc '%u' "$AT_DENY")
            FILE_GID_NAME=$(stat -Lc '%G' "$AT_DENY")

            a_output_info+=("  - Actual state for $AT_DENY: $(stat -Lc 'Access: (%#a/%A) Owner: (%U) Group: (%G)' "$AT_DENY")")

            # Check Permissions
            if [ $(( $PERMS & 0037 )) -eq 0 ]; then
                a_output_pass+=("  - $AT_DENY: Permissions ('$PERMS') are 640 or more restrictive.")
            else
                a_output_fail+=("  - $AT_DENY: Permissions ('$PERMS') are NOT 640 or more restrictive.")
            fi
            # Check Owner
            if [ "$FILE_UID" -eq 0 ]; then
                a_output_pass+=("  - $AT_DENY: Owner is 'root'.")
            else
                a_output_fail+=("  - $AT_DENY: Owner is not 'root'.")
            fi
            # Check Group
            if [ "$FILE_GID_NAME" == "root" ] || [ "$FILE_GID_NAME" == "daemon" ]; then
                a_output_pass+=("  - $AT_DENY: Group is '$FILE_GID_NAME', which is compliant.")
            else
                a_output_fail+=("  - $AT_DENY: Group is '$FILE_GID_NAME', but should be 'root' or 'daemon'.")
            fi
        fi
    fi

    # --- Display Results ---
    if [ "${#a_output_info[@]}" -gt 0 ]; then
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "${a_output_info[@]}"
    fi

    if [ "${#a_output_fail[@]}" -le 0 ]; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "${a_output_pass[@]}"
    else
        printf '\n%s\n' "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "${a_output_fail[@]}"

        if [ "${#a_output_pass[@]}" -gt 0 ]; then
            printf '\n%s\n' "- Correctly set:"
            printf '%s\n' "${a_output_pass[@]}"
        fi

        # --- Remediation Section ---
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure /etc/cron.allow and /etc/at.allow exist and are correctly configured."
        printf '%s\n' "Run the following commands to apply recommended settings:"
        printf '%s\n' "# sudo rm /etc/cron.deny /etc/at.deny &>/dev/null"
        printf '%s\n' "# sudo touch /etc/cron.allow /etc/at.allow"
        printf '%s\n' "# sudo chmod u-x,g-wx,o-rwx /etc/cron.allow /etc/at.allow"
        printf '%s\n' "# sudo chown root:root /etc/cron.allow /etc/at.allow"
    fi
}
BASH
    ],

];
