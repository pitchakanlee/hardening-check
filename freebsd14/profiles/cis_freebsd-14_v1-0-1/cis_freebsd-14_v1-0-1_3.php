<?php
// =============================================================
// == file: CIS_FreeBSD_14_Benchmark_v1.0.3.pdf
// =============================================================
return [
    [ 'id' => '3', 'title' => 'Network', 'type' => 'header'],

    ['id' => '3.1', 'title' => 'Configure Network Devices', 'type' => 'header'],

    [ 'id' => '3.1.1', 'title' => 'Ensure IPv6 status is identified', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_INFO=""

    ipv6_status=$(sysctl -n kern.features.inet6)

    if [ "$ipv6_status" = "1" ]; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - IPv6 is enabled in the running kernel (kern.features.inet6=1).")
    else
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - IPv6 is not enabled in the running kernel (kern.features.inet6=0).")
    fi

    printf '%s\n' "" "-- INFO --"
    printf '%s\n' "$OUTPUT_INFO"
    
    printf '\n\n%s\n' "-- Suggestion --"
    printf '%s\n' "Enable or disable IPv6 in accordance with your system requirements and local site policy."

    printf '\n%s\n' "- Audit Result:" "  ** MANUAL **"
    printf '%s\n' "  - Please review the status above and ensure it complies with your site policy."
}
BASH
    ],

    ['id' => '3.2', 'title' => 'Configure Network Kernel Modules', 'type' => 'header'],

    [ 'id' => '3.2.1', 'title' => 'Ensure sctp kernel module is not available', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    MODULE_NAME="sctp"
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
        printf '%s\n' "To prevent the module from loading on boot, run the following command:"
        printf '%s\n' "# printf 'module_blacklist=\"sctp\"\\n' >> /boot/loader.conf"
        printf '%s\n' "A reboot is recommended to ensure the module is unloaded."
    fi
}
BASH
    ],
    
    ['id' => '3.3', 'title' => 'Configure Network Kernel Parameters', 'type' => 'header'],

    [ 'id' => '3.3.1', 'title' => 'Ensure ip forwarding is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""

    ipv4_sysctl_val=$(sysctl -n net.inet.ip.forwarding)
    if [ "$ipv4_sysctl_val" = "0" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv4 forwarding is disabled in running configuration (net.inet.ip.forwarding=0).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - IPv4 forwarding is enabled in running configuration (net.inet.ip.forwarding=1).")
    fi

    ipv4_rc_val=$(sysrc -n gateway_enable)
    if [ "$ipv4_rc_val" != "YES" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv4 gateway is not enabled on boot (gateway_enable!=YES).")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - IPv4 gateway is enabled on boot (gateway_enable=YES).")
    fi

    if sysctl net.inet6.ip6.forwarding >/dev/null 2>&1; then
        OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - IPv6 is enabled. Checking forwarding settings...")

        ipv6_sysctl_val=$(sysctl -n net.inet6.ip6.forwarding)
        if [ "$ipv6_sysctl_val" = "0" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv6 forwarding is disabled in running configuration (net.inet6.ip6.forwarding=0).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - IPv6 forwarding is enabled in running configuration (net.inet6.ip6.forwarding=1).")
        fi

        ipv6_rc_val=$(sysrc -n ipv6_gateway_enable)
        if [ "$ipv6_rc_val" != "YES" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv6 gateway is not enabled on boot (ipv6_gateway_enable!=YES).")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - IPv6 gateway is enabled on boot (ipv6_gateway_enable=YES).")
        fi
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv6 appears to be disabled; IPv6 forwarding checks are not applicable.")
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
        printf '%s\n' "To disable IP forwarding, run the relevant commands:"
        printf '%s\n' "# service gateway onedisable"
        printf '%s\n' "# service ipv6_gateway onedisable"
        printf '%s\n' "# sysctl net.inet.ip.forwarding=0"
        printf '%s\n' "# sysctl net.inet6.ip6.forwarding=0"
    fi
}
BASH
    ],

    [ 'id' => '3.3.2', 'title' => 'Ensure packet redirect sending is disabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"

    PARAM_IPV4="net.inet.ip.redirect"
    EXPECTED_VALUE="0"
    
    running_val_ipv4=$(sysctl -n "$PARAM_IPV4")
    if [ "$running_val_ipv4" = "$EXPECTED_VALUE" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_IPV4' is correctly set to '0'.")
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_IPV4' is '$running_val_ipv4', but should be '0'.")
    fi

    config_setting_ipv4=$(grep -E "^\s*${PARAM_IPV4}\s*=" "$CONFIG_FILE" | tail -n 1)
    if [ -n "$config_setting_ipv4" ]; then
        config_val=$(echo "$config_setting_ipv4" | awk -F= '{print $2}' | xargs)
        if [ "$config_val" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_IPV4' is correctly set to '0'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_IPV4' is '$config_val', but should be '0'.")
        fi
    else
         OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_IPV4' is not set in '$CONFIG_FILE'.")
    fi

    PARAM_IPV6="net.inet6.ip6.redirect"
    if sysctl "$PARAM_IPV6" >/dev/null 2>&1; then
        running_val_ipv6=$(sysctl -n "$PARAM_IPV6")
        if [ "$running_val_ipv6" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_IPV6' is correctly set to '0'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_IPV6' is '$running_val_ipv6', but should be '0'.")
        fi
        
        config_setting_ipv6=$(grep -E "^\s*${PARAM_IPV6}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting_ipv6" ]; then
            config_val=$(echo "$config_setting_ipv6" | awk -F= '{print $2}' | xargs)
            if [ "$config_val" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_IPV6' is correctly set to '0'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_IPV6' is '$config_val', but should be '0'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_IPV6' is not set in '$CONFIG_FILE'.")
        fi
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv6 is disabled, '$PARAM_IPV6' check is not applicable.")
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
        printf '%s\n' "To disable packet redirect sending, add the following lines to '$CONFIG_FILE':"
        printf '%s\n' "net.inet.ip.redirect=0"
        printf '%s\n' "net.inet6.ip6.redirect=0"
        printf '%s\n' "Then, apply the settings to the running configuration:"
        printf '%s\n' "# sysctl net.inet.ip.redirect=0"
        printf '%s\n' "# sysctl net.inet6.ip6.redirect=0"
    fi
}
BASH
    ],

    [ 'id' => '3.3.3', 'title' => 'Ensure broadcast & multicast icmp requests are ignored', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"
    
    check_sysctl_param() {
        PARAM_NAME=$1
        EXPECTED_VALUE=$2

        running_value=$(sysctl -n "$PARAM_NAME")
        if [ "$running_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_NAME' is '$running_value', but should be '$EXPECTED_VALUE'.")
        fi

        config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting" ]; then
            config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
            if [ "$config_value" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_NAME' is '$config_value', but should be '$EXPECTED_VALUE'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
        fi
    }

    check_sysctl_param "net.inet.icmp.bmcastecho" "0"
    check_sysctl_param "net.inet.icmp.tstamprepl" "0"


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
        printf '%s\n' "To disable these ICMP requests, add the following lines to '$CONFIG_FILE':"
        printf '%s\n' "net.inet.icmp.bmcastecho=0"
        printf '%s\n' "net.inet.icmp.tstamprepl=0"
        printf '%s\n' "Then, apply the settings to the running configuration:"
        printf '%s\n' "# sysctl net.inet.icmp.bmcastecho=0"
        printf '%s\n' "# sysctl net.inet.icmp.tstamprepl=0"
    fi
}
BASH
    ],

    [ 'id' => '3.3.4', 'title' => 'Ensure icmp redirects are not accepted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"

    check_sysctl_param() {
        PARAM_NAME=$1
        EXPECTED_VALUE=$2

        running_value=$(sysctl -n "$PARAM_NAME" 2>/dev/null)
        if [ "$running_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_NAME' is '$running_value', but should be '$EXPECTED_VALUE'.")
        fi

        config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting" ]; then
            config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
            if [ "$config_value" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_NAME' is '$config_value', but should be '$EXPECTED_VALUE'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
        fi
    }

    check_sysctl_param "net.inet.icmp.drop_redirect" "1"

    if sysctl net.inet6.icmp6.rediraccept >/dev/null 2>&1; then
        check_sysctl_param "net.inet6.icmp6.rediraccept" "0"
    else
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - IPv6 is disabled, 'net.inet6.icmp6.rediraccept' check is not applicable.")
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
        printf '%s\n' "To disable ICMP redirects, add the following lines to '$CONFIG_FILE':"
        printf '%s\n' "net.inet.icmp.drop_redirect=1"
        printf '%s\n' "net.inet6.icmp6.rediraccept=0"
        printf '%s\n' "Then, apply the settings to the running configuration:"
        printf '%s\n' "# sysctl net.inet.icmp.drop_redirect=1"
        printf '%s\n' "# sysctl net.inet6.icmp6.rediraccept=0"
    fi
}
BASH
    ],

    [ 'id' => '3.3.5', 'title' => 'Ensure source routed packets are not accepted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"
    PARAM_NAME="net.inet.ip.accept_sourceroute"
    EXPECTED_VALUE="0"

    check_sysctl_param() {
        running_value=$(sysctl -n "$PARAM_NAME" 2>/dev/null)
        if [ "$running_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_NAME' is '$running_value', but should be '$EXPECTED_VALUE'.")
        fi

        config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting" ]; then
            config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
            if [ "$config_value" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_NAME' is '$config_value', but should be '$EXPECTED_VALUE'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
        fi
    }

    check_sysctl_param

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
        printf '%s\n' "To disable acceptance of source-routed packets, add the following line to '$CONFIG_FILE':"
        printf '%s\n' "net.inet.ip.accept_sourceroute=0"
        printf '%s\n' "Then, apply the setting to the running configuration:"
        printf '%s\n' "# sysctl net.inet.ip.accept_sourceroute=0"
    fi
}
BASH
    ],

    [ 'id' => '3.3.6', 'title' => 'Ensure tcp syn cookies is enabled', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"
    PARAM_NAME="net.inet.tcp.syncookies"
    EXPECTED_VALUE="1"

    check_sysctl_param() {
        running_value=$(sysctl -n "$PARAM_NAME" 2>/dev/null)
        if [ "$running_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_NAME' is '$running_value', but should be '$EXPECTED_VALUE'.")
        fi

        config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting" ]; then
            config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
            if [ "$config_value" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_NAME' is '$config_value', but should be '$EXPECTED_VALUE'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
        fi
    }

    check_sysctl_param

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
        printf '%s\n' "To enable TCP SYN Cookies, add the following line to '$CONFIG_FILE':"
        printf '%s\n' "net.inet.tcp.syncookies=1"
        printf '%s\n' "Then, apply the setting to the running configuration:"
        printf '%s\n' "# sysctl net.inet.tcp.syncookies=1"
    fi
}
BASH
    ],

    [ 'id' => '3.3.7', 'title' => 'Ensure ipv6 router advertisements are not accepted', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    CONFIG_FILE="/etc/sysctl.conf"
    PARAM_NAME="net.inet6.ip6.accept_rtadv"
    EXPECTED_VALUE="0"

    if ! sysctl "$PARAM_NAME" >/dev/null 2>&1; then
        printf '\n%s\n' "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - IPv6 is disabled, this check is not applicable."
    else
        
        running_value=$(sysctl -n "$PARAM_NAME" 2>/dev/null)
        if [ "$running_value" = "$EXPECTED_VALUE" ]; then
            OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Running config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
        else
            OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Running config for '$PARAM_NAME' is '$running_value', but should be '$EXPECTED_VALUE'.")
        fi

        config_setting=$(grep -E "^\s*${PARAM_NAME}\s*=" "$CONFIG_FILE" | tail -n 1)
        if [ -n "$config_setting" ]; then
            config_value=$(echo "$config_setting" | awk -F= '{print $2}' | xargs)
            if [ "$config_value" = "$EXPECTED_VALUE" ]; then
                OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Persistent config for '$PARAM_NAME' is correctly set to '$EXPECTED_VALUE'.")
            else
                OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Persistent config for '$PARAM_NAME' is '$config_value', but should be '$EXPECTED_VALUE'.")
            fi
        else
             OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - '$PARAM_NAME' is not set in '$CONFIG_FILE'.")
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
            printf '%s\n' "To disable acceptance of IPv6 router advertisements, add the following line to '$CONFIG_FILE':"
            printf '%s\n' "net.inet6.ip6.accept_rtadv=0"
            printf '%s\n' "Then, apply the setting to the running configuration:"
            printf '%s\n' "# sysctl net.inet6.ip6.accept_rtadv=0"
        fi
    fi
}
BASH
    ],

    ['id' => '3.4', 'title' => 'Configure Host Based Firewall', 'type' => 'header'],

    [ 'id' => '3.4.1', 'title' => 'Configure a firewall utility', 'type' => 'header'],
    
    [ 'id' => '3.4.1.1', 'title' => 'Ensure ipfw is enabled and configured', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    RC_VAR="firewall_enable"

    firewall_status=$(sysrc -n "$RC_VAR")
    
    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Current status of '$RC_VAR': $firewall_status")

    if [ "$firewall_status" = "YES" ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - The '$RC_VAR' service is enabled.")
        
        if service ipfw status >/dev/null 2>&1; then
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - 'ipfw' service is currently running.")
        else
            OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - 'ipfw' service is enabled but not currently running.")
        fi
    else
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - The '$RC_VAR' service is not enabled ('$firewall_status').")
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
        printf '%s\n' "To enable and configure a secure default firewall, run:"
        printf '%s\n' "# sysrc firewall_enable=\"YES\""
        printf '%s\n' "# sysrc firewall_type=\"workstation\""
        printf '%s\n' "# sysrc firewall_myservices+=\"22/tcp\""
        printf '%s\n' "# sysrc firewall_allowservices=\"any\""
        printf '%s\n' "Then, start the service:"
        printf '%s\n' "# service ipfw start"
    fi
}
BASH
    ],

    [ 'id' => '3.4.1.2', 'title' => 'Ensure a single firewall utility is in use', 'profile' => 'Level 1', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/bin/sh
{
    OUTPUT_PASS=""
    OUTPUT_FAIL=""
    OUTPUT_INFO=""
    
    enabled_count=0
    enabled_firewalls=""

    fw_ipfw=$(sysrc -n firewall_enable)
    fw_ipfilter=$(sysrc -n ipfilter_enable)
    fw_pf=$(sysrc -n pf_enable)

    if [ "$fw_ipfw" = "YES" ]; then
        enabled_count=$((enabled_count + 1))
        enabled_firewalls="$enabled_firewalls ipfw"
    fi
    if [ "$fw_ipfilter" = "YES" ]; then
        enabled_count=$((enabled_count + 1))
        enabled_firewalls="$enabled_firewalls ipfilter"
    fi
    if [ "$fw_pf" = "YES" ]; then
        enabled_count=$((enabled_count + 1))
        enabled_firewalls="$enabled_firewalls pf"
    fi

    OUTPUT_INFO=$(printf "%s\n%s" "$OUTPUT_INFO" "  - Found $enabled_count enabled firewall(s):$enabled_firewalls")

    if [ "$enabled_count" -eq 1 ]; then
        OUTPUT_PASS=$(printf "%s\n%s" "$OUTPUT_PASS" "  - Exactly one firewall is enabled, which is compliant.")
    elif [ "$enabled_count" -gt 1 ]; then
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - Multiple firewalls are enabled. Only one should be active.")
    else # count is 0
        OUTPUT_FAIL=$(printf "%s\n%s" "$OUTPUT_FAIL" "  - No firewall is enabled in rc.conf.")
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
        printf '%s\n' "Ensure exactly one firewall is enabled. To disable an unwanted firewall, run:"
        printf '%s\n' "# service <service_name> onestop"
        printf '%s\n' "# service <service_name> onedisable"
        printf '%s\n' "(Replace <service_name> with 'ipfw', 'ipfilter', or 'pf')"
    fi
}
BASH
    ],
];
