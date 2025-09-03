<?php
// =============================================================
// == file: CIS_Debian_Linux_12_Benchmark_v1.1.0.pdf
// == section: 4
// =============================================================
return [
    // --- 4 Host Based Firewall ---
    [ 'id' => '4', 'title' => 'Host Based Firewall', 'type' => 'header' ],

    // --- 4.1 Configure a single firewall utility ---
    [ 'id' => '4.1', 'title' => 'Configure a single firewall utility', 'type' => 'header' ],

    // --- 4.1.1 Ensure a single firewall configuration utility is in use ---
    [
        'id' => '4.1.1', 'title' => 'Ensure a single firewall configuration utility is in use', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   active_firewall=() firewalls=("ufw" "nftables" "iptables")
   for firewall in "${firewalls[@]}"; do
      case $firewall in
         nftables)
            cmd="nft" ;;
         *)
            cmd=$firewall ;;
      esac
      if command -v $cmd &> /dev/null && systemctl is-enabled --quiet $firewall && systemctl is-active --quiet $firewall; then
         active_firewall+=("$firewall")
      fi
   done

   if [ ${#active_firewall[@]} -eq 1 ]; then
      printf '%s\n' "" "Audit Results:" " ** PASS **" " - A single firewall is in use follow the recommendation in ${active_firewall[0]} subsection ONLY"
   elif [ ${#active_firewall[@]} -eq 0 ]; then
      printf '%s\n' "" " Audit Results:" " ** FAIL **" "- No firewall in use or unable to determine firewall status"
   else
      printf '%s\n' "" " Audit Results:" " ** FAIL **" " - Multiple firewalls are in use: ${active_firewall[*]}"
   fi
}
BASH
    ],

    // --- 4.2 Configure UncomplicatedFirewall ---
    [ 'id' => '4.2', 'title' => 'Configure UncomplicatedFirewall', 'type' => 'header' ],

    // --- 4.2.1 Ensure ufw is installed ---
    [
        'id' => '4.1.1', 'title' => 'Ensure a single firewall configuration utility is in use', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
        'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
   has_ufw="false"
   has_iptables="false"
   has_nftables="false"

   if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
   if dpkg-query -s "iptables" &>/dev/null; then has_iptables="true"; fi
   if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

   conflicting_firewalls=0
   [ "$has_ufw" == "true" ] && ((conflicting_firewalls++))
   [ "$has_nftables" == "true" ] && ((conflicting_firewalls++))


   if [ "$conflicting_firewalls" -gt 1 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
      printf '%s\n' " - Reason(s) for audit failure:"
      printf '%s\n' "  - Multiple conflicting firewall utilities are installed. Only one should be active."
      printf '%s\n' "    - Found: ufw"
      printf '%s\n' "    - Found: nftables"

   elif [ "$conflicting_firewalls" -eq 0 ]; then
      printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
      printf '%s\n' " - Reason(s) for audit failure:"
      printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
      printf '\n\n%s\n' "-- Suggestion --"
      printf '%s\n' "A firewall is required. To install the recommended firewall (UFW), run:"
      printf '%s\n' "# sudo apt install ufw"

    elif [ "$has_ufw" == "true" ]; then
      printf '%s\n' "" "- Audit Result:" "  ** PASS **"
      printf '%s\n' "  - 'ufw' is installed and is the only active firewall utility."
   elif [ "$has_nftables" == "true" ]; then
      printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
      printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
      printf '%s\n' "  - This check for UFW is not applicable. Please audit nftables configuration separately."
   fi
}
BASH
    ],

    // --- 4.2.2 Ensure iptables-persistent is not installed with ufw ---
    [
        'id' => '4.2.2', 'title' => 'Ensure iptables-persistent is not installed with ufw', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A firewall is required. To install the recommended firewall (UFW), run:"
        printf '%s\n' "# sudo apt install ufw"

    elif [ "$has_ufw" == "true" ]; then
        if dpkg-query -s "iptables-persistent" &>/dev/null; then
            printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
            printf '%s\n' " - Reason(s) for audit failure:"
            printf '%s\n' "  - 'ufw' is installed, but the conflicting package 'iptables-persistent' is also present."
            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Run the following command to remove the conflicting package:"
            printf '%s\n' "# sudo apt purge iptables-persistent"
        else
            printf '%s\n' "" "- Audit Result:" "  ** PASS **"
            printf '%s\n' "  - 'ufw' is installed as the sole firewall utility."
            printf '%s\n' "  - Conflicting package 'iptables-persistent' is not installed."
        fi

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
        printf '%s\n' "  - This check for UFW is not applicable. Please audit nftables configuration separately."
    fi
}
BASH
    ],

    // --- 4.2.3 Ensure ufw service is enabled ---
    [
        'id' => '4.2.3', 'title' => 'Ensure ufw service is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A firewall is required. To install the recommended firewall (UFW), run:"
        printf '%s\n' "# sudo apt install ufw"

    elif [ "$has_ufw" == "true" ]; then
        a_output_pass=()
        a_output_fail=()

        if systemctl is-enabled ufw.service 2>/dev/null | grep -q 'enabled'; then
            a_output_pass+=("  - 'ufw.service' is enabled.")
        else
            a_output_fail+=("  - 'ufw.service' is not enabled.")
        fi

        if systemctl is-active ufw.service 2>/dev/null | grep -q 'active'; then
            a_output_pass+=("  - 'ufw.service' is active.")
        else
            a_output_fail+=("  - 'ufw.service' is not active.")
        fi

        if sudo ufw status | grep -q "Status: active"; then
            a_output_pass+=("  - 'ufw status' reports as active.")
        else
            a_output_fail+=("  - 'ufw status' does not report as active.")
        fi

        if [ "${#a_output_fail[@]}" -le 0 ]; then
            printf '%s\n' "" "- Audit Result:" "  ** PASS **"
            printf '%s\n' "  - UFW is installed and correctly configured."
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
            printf '%s\n' "Run the following commands to enable and start ufw:"
            printf '%s\n' "# sudo systemctl unmask ufw.service"
            printf '%s\n' "# sudo systemctl --now enable ufw.service"
            printf '%s\n' "# sudo ufw enable"
        fi
    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
        printf '%s\n' "  - This check for UFW is not applicable. Please audit nftables configuration separately."
    fi
}
BASH
    ],

    // --- 4.2.4 Ensure ufw loopback traffic is configured ---
    [
        'id' => '4.2.4', 'title' => 'Ensure ufw loopback traffic is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A firewall is required. To install the recommended firewall (UFW), run:"
        printf '%s\n' "# sudo apt install ufw"

    elif [ "$has_ufw" == "true" ]; then
        a_output_pass=()
        a_output_fail=()
        a_output_info=()
        UFW_RULES_FILE="/etc/ufw/before.rules"

        if ! sudo ufw status | grep -q "Status: active"; then
            a_output_fail+=("  - UFW is not active. This check cannot be performed accurately.")
        else
            a_output_info+=("  - UFW is active. Proceeding with rule checks.")

            if grep -Pqs -- "^\h*-\s*A\s+ufw-before-input\s+-i\s+lo\s+-j\s+ACCEPT" "$UFW_RULES_FILE"; then
                a_output_pass+=("  - Loopback input traffic is correctly accepted in $UFW_RULES_FILE.")
            else
                a_output_fail+=("  - Loopback input traffic is NOT configured to be accepted in $UFW_RULES_FILE.")
            fi

            if grep -Pqs -- "^\h*-\s*A\s+ufw-before-output\s+-o\s+lo\s+-j\s+ACCEPT" "$UFW_RULES_FILE"; then
                a_output_pass+=("  - Loopback output traffic is correctly accepted in $UFW_RULES_FILE.")
            else
                a_output_fail+=("  - Loopback output traffic is NOT configured to be accepted in $UFW_RULES_FILE.")
            fi

            UFW_STATUS_VERBOSE=$(sudo ufw status verbose)

            if echo "$UFW_STATUS_VERBOSE" | grep -Pqs -- "^\s*Anywhere\s+DENY\s+IN\s+127\.0\.0\.0/8"; then
                a_output_pass+=("  - Traffic from the IPv4 loopback network (127.0.0.0/8) is correctly denied.")
            else
                a_output_fail+=("  - Traffic from the IPv4 loopback network (127.0.0.0/8) is NOT explicitly denied.")
            fi

            if echo "$UFW_STATUS_VERBOSE" | grep -Pqs -- "^\s*Anywhere\s+\(v6\)\s+DENY\s+IN\s+::1"; then
                a_output_pass+=("  - Traffic from the IPv6 loopback network (::1) is correctly denied.")
            else
                a_output_fail+=("  - Traffic from the IPv6 loopback network (::1) is NOT explicitly denied.")
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
            printf '%s\n' "Run the following commands to configure loopback traffic correctly:"
            printf '%s\n' "# sudo ufw allow in on lo"
            printf '%s\n' "# sudo ufw allow out on lo"
            printf '%s\n' "# sudo ufw deny in from 127.0.0.0/8"
            printf '%s\n' "# sudo ufw deny in from ::1"
        fi

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
        printf '%s\n' "  - This check for UFW is not applicable. Please audit nftables configuration separately."
    fi
}
BASH
    ],

    // --- 4.2.5 Ensure ufw outbound connections are configured ---
    [
        'id' => '4.2.5', 'title' => 'Ensure ufw outbound connections are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A firewall is required. To install the recommended firewall (UFW), run:"
        printf '%s\n' "# sudo apt install ufw"

    elif [ "$has_ufw" == "true" ]; then
      printf '%s\n' "" "-- INFO --"
      printf '%s\n' "Review the following outbound connection rules to ensure they match your site policy."
      printf '%s\n' "--------------------------------------------------------------------------------"

      ufw status numbered

      printf '%s\n' "--------------------------------------------------------------------------------"

      printf '\n\n%s\n' "-- Suggestion --"
      printf '%s\n' "To implement a default policy that allows all outbound connections, run:"
      printf '%s\n' "# sudo ufw allow out on all"

      printf '\n%s\n' "** REVIEW ** Required: Manually review the output above against your site policy."

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
        printf '%s\n' "  - This check for UFW is not applicable. Please audit nftables configuration separately."
    fi
}
BASH
    ],

    // --- 4.2.6 Ensure ufw firewall rules exist for all open ports ---
    [
        'id' => '4.2.6', 'title' => 'Ensure ufw firewall rules exist for all open ports', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."

    elif [ "$has_ufw" == "true" ]; then
        # This is the section with the corrected logic
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        ufw_ports=($(sudo ufw status verbose | grep -Po '^\h*\d+\b' | sort -u))

        open_ports=($(ss -tuln | awk '($5!~/%lo:/ && $5!~/127.0.0.1:/ && $5!~/\[?::1\]?:/) {split($5, a, ":"); print a[length(a)]}' | sort -u))

        a_output_info+=("  - Ports with UFW rules: ${ufw_ports[*]}")
        a_output_info+=("  - All open network ports: ${open_ports[*]}")

        unruled_ports=($(comm -23 <(printf '%s\n' "${open_ports[@]}") <(printf '%s\n' "${ufw_ports[@]}")))

        if [ ${#unruled_ports[@]} -gt 0 ]; then
            a_output_fail+=("  - The following open port(s) have no explicit UFW rule: ${unruled_ports[*]}")
        else
            a_output_pass+=("  - All open network ports are covered by UFW rules.")
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
        fi

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
        printf '%s\n' "  - This check for UFW is not applicable."
    fi
}
BASH
    ],

    // --- 4.2.7 Ensure ufw default deny firewall policy ---
    [
        'id' => '4.2.7', 'title' => 'Ensure ufw default deny firewall policy', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw="false"
    has_nftables="false"

    if dpkg-query -s "ufw" &>/dev/null; then has_ufw="true"; fi
    if dpkg-query -s "nftables" &>/dev/null; then has_nftables="true"; fi

    if [ "$has_ufw" == "true" ] && [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - Multiple conflicting firewall utilities are installed (ufw and nftables)."

    elif [ "$has_ufw" == "false" ] && [ "$has_nftables" == "false" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."

    elif [ "$has_ufw" == "true" ]; then
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        if ! sudo ufw status | grep -q "Status: active"; then
            a_output_fail+=("  - UFW is not active. Dependent checks cannot be performed accurately.")
        else
            a_output_info+=("  - UFW is active. Proceeding with rule checks.")

            default_policy_line=$(sudo ufw status verbose | grep 'Default:')

            incoming_policy=$(echo "$default_policy_line" | grep -oP 'deny|reject|disabled(?= \(incoming\))')
            if [[ "$incoming_policy" == "deny" || "$incoming_policy" == "reject" ]]; then
                a_output_pass+=("  - Default incoming policy is compliant ('$incoming_policy').")
            else
                a_output_fail+=("  - Default incoming policy is not compliant.")
            fi

            ufw_ports=($(sudo ufw status verbose | grep -Po '^\h*\d+\b' | sort -u))
            open_ports=($(ss -tuln | awk '($5!~/%lo:/ && $5!~/127.0.0.1:/ && $5!~/\[?::1\]?:/) {split($5, a, ":"); print a[length(a)]}' | sort -u))

            a_output_info+=("  - Ports with UFW rules: ${ufw_ports[*]}")
            a_output_info+=("  - All open network ports: ${open_ports[*]}")

            unruled_ports=($(comm -23 <(printf '%s\n' "${open_ports[@]}") <(printf '%s\n' "${ufw_ports[@]}")))

            if [ ${#unruled_ports[@]} -gt 0 ]; then
                a_output_fail+=("  - The following open port(s) have no explicit UFW rule: ${unruled_ports[*]}")
            else
                a_output_pass+=("  - All open network ports are covered by UFW rules.")
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
        fi

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'nftables' is installed, not 'ufw'."
    fi
}
BASH
    ],

    // --- 4.3 Configure nftables ---
    [ 'id' => '4.3', 'title' => 'Configure nftables', 'type' => 'header' ],

    // --- 4.3.1 Ensure nftables is installed ---
    [
        'id' => '4.3.1', 'title' => 'Ensure nftables is installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    has_ufw=$(dpkg-query -s "ufw" &>/dev/null && echo "true" || echo "false")
    has_nftables=$(dpkg-query -s "nftables" &>/dev/null && echo "true" || echo "false")

    if [ "$has_ufw" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: 'ufw' is installed and is considered the primary firewall."
        printf '%s\n' "  - This check for nftables is not applicable."

    elif [ "$has_nftables" == "true" ]; then
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - 'nftables' is installed."
        printf '%s\n' "  - Note: Ensure it is properly configured in a subsequent check."

    else
        printf '%s\n' "" "- Audit Result:" "  ** FAIL **"
        printf '%s\n' " - Reason(s) for audit failure:"
        printf '%s\n' "  - No primary firewall utility (ufw or nftables) is installed."
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "A firewall is required. To install nftables, run:"
        printf '%s\n' "# sudo apt install nftables"
    fi
}
BASH
    ],

    // --- 4.3.2 Ensure ufw is uninstalled or disabled with nftables ---
    [
        'id' => '4.3.2', 'title' => '4.2.3 Ensure ufw is uninstalled or disabled with nftables', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is only applicable when nftables is the chosen firewall."
    else
        a_output_info+=("  - Package 'nftables' is installed. Verifying that UFW is not active.")

        if ! dpkg-query -s "ufw" &>/dev/null; then
            a_output_pass+=("  - Conflicting package 'ufw' is not installed.")
        else
            is_inactive="false"
            is_masked="false"

            ufw_status_output=$(sudo ufw status 2>/dev/null)
            if echo "$ufw_status_output" | grep -q "Status: inactive"; then
                a_output_pass+=("  - 'ufw status' is inactive.")
                is_inactive="true"
            else
                a_output_fail+=("  - 'ufw status' is not inactive. Current status: $(echo "$ufw_status_output" | head -n 1)")
            fi

            service_status=$(systemctl is-enabled ufw.service 2>/dev/null)
            if [ "$service_status" == "masked" ]; then
                a_output_pass+=("  - 'ufw.service' is masked.")
                is_masked="true"
            else
                a_output_fail+=("  - 'ufw.service' is not masked (current state: '$service_status').")
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
            printf '%s\n' "To ensure nftables is the only active firewall, run the following commands to disable UFW:"
            printf '%s\n' "# sudo ufw disable"
            printf '%s\n' "# sudo systemctl --now mask ufw.service"
        fi
    fi
}
BASH
    ],

    // --- 4.3.3 Ensure iptables are flushed with nftables ---
    [
        'id' => '4.3.3', 'title' => 'Ensure iptables are flushed with nftables', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => true, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check for legacy iptables rules is not applicable."
    else
        a_output_info+=("  - 'nftables' is installed. Checking for legacy iptables rules...")

        iptables_rules=$(sudo iptables -S | grep -v -- '-P ')
        if [ -z "$iptables_rules" ]; then
            a_output_pass+=("  - No custom iptables (IPv4) rules exist.")
        else
            a_output_fail+=("  - Custom iptables (IPv4) rules exist.")
            a_output_info+=("  - Existing IPv4 Rules:")
            while IFS= read -r line; do
                a_output_info+=("    $line")
            done <<< "$iptables_rules"
        fi

        ip6tables_rules=$(sudo ip6tables -S | grep -v -- '-P ')
        if [ -z "$ip6tables_rules" ]; then
            a_output_pass+=("  - No custom ip6tables (IPv6) rules exist.")
        else
            a_output_fail+=("  - Custom ip6tables (IPv6) rules exist.")
            a_output_info+=("  - Existing IPv6 Rules:")
            while IFS= read -r line; do
                a_output_info+=("    $line")
            done <<< "$ip6tables_rules"
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
            printf '%s\n' "Run the following commands to flush all existing legacy rules:"
            printf '%s\n' "# sudo iptables -F"
            printf '%s\n' "# sudo ip6tables -F"
        fi
    fi
}
BASH
    ],

    // --- 4.3.4 Ensure a nftables table exists ---
    [
        'id' => '4.3.4', 'title' => 'Ensure a nftables table exists', 'profile' => 'Level 1 - Server', 'type' => 'Automated',
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        a_output_info+=("  - Package 'nftables' is installed. Checking for existing tables...")

        nft_tables_output=$(nft list tables 2>/dev/null)

        if [ -n "$nft_tables_output" ]; then
            a_output_pass+=("  - At least one nftables table exists.")
            a_output_info+=("  - Existing Tables:")
            while IFS= read -r line; do
                a_output_info+=("    $line")
            done <<< "$nft_tables_output"
        else
            a_output_fail+=("  - No nftables tables were found.")
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

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Run the following command to create a basic table:"
            printf '%s\n' "# sudo nft create table inet filter"
        fi
    fi
}
BASH
    ],

    // --- 4.3.5 Ensure nftables base chains exist ---
    [
        'id' => '4.3.5', 'title' => 'Ensure nftables base chains exist', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        a_output_info+=("  - Package 'nftables' is installed. Checking for base chains...")

        ruleset=$(nft list ruleset)

        if echo "$ruleset" | grep -q 'hook input'; then
            a_output_pass+=("  - Base chain for 'hook input' exists.")
        else
            a_output_fail+=("  - Base chain for 'hook input' does NOT exist.")
        fi

        if echo "$ruleset" | grep -q 'hook forward'; then
            a_output_pass+=("  - Base chain for 'hook forward' exists.")
        else
            a_output_fail+=("  - Base chain for 'hook forward' does NOT exist.")
        fi

        if echo "$ruleset" | grep -q 'hook output'; then
            a_output_pass+=("  - Base chain for 'hook output' exists.")
        else
            a_output_fail+=("  - Base chain for 'hook output' does NOT exist.")
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
            printf '%s\n' "Run the following commands to create the missing base chains:"
            printf '%s\n' "# sudo nft add table inet filter"
            printf '%s\n' "# sudo nft add chain inet filter input { type filter hook input priority 0 \; } "
            printf '%s\n' "# sudo nft add chain inet filter forward { type filter hook forward priority 0 \; } "
            printf '%s\n' "# sudo nft add chain inet filter output { type filter hook output priority 0 \; } "
        fi
    fi
}
BASH
    ],

    // --- 4.3.6 Ensure nftables loopback traffic is configured  ---
    [
        'id' => '4.3.6', 'title' => 'Ensure nftables loopback traffic is configured ', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        a_output_info+=("  - Package 'nftables' is installed. Checking ruleset...")
        
        ruleset=$(nft list ruleset)
        input_rules=$(echo "$ruleset" | awk '/hook input/,/}/')

        if echo "$input_rules" | grep -Pqs -- '^\s*iif\s+"lo"\s+accept\b'; then
            a_output_pass+=("  - Loopback interface correctly accepts traffic (iif \"lo\" accept).")
        else
            a_output_fail+=("  - Loopback interface is not configured to accept traffic.")
        fi

        if echo "$input_rules" | grep -Pqs -- '^\s*ip\s+saddr\s+127\.0\.0\.0/8\s+drop\b'; then
            a_output_pass+=("  - IPv4 loopback traffic is correctly configured to be dropped.")
        else
            a_output_fail+=("  - IPv4 loopback traffic is not configured to be dropped.")
        fi

        if echo "$input_rules" | grep -Pqs -- '^\s*ip6\s+saddr\s+::1\s+drop\b'; then
            a_output_pass+=("  - IPv6 loopback traffic is correctly configured to be dropped.")
        else
            if [ -f /proc/net/if_inet6 ]; then
                a_output_fail+=("  - IPv6 loopback traffic is not configured to be dropped.")
            else
                a_output_pass+=("  - IPv6 is disabled, skipping IPv6 loopback check.")
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
        printf '%s\n' "Add the following rules to your nftables ruleset to correctly configure loopback traffic:"
        printf '%s\n' '# sudo nft add rule inet filter input iif "lo" accept'
        printf '%s\n' '# sudo nft add rule inet filter input ip saddr 127.0.0.0/8 drop'
        printf '%s\n' '# sudo nft add rule inet filter input ip6 saddr ::1 drop'
    fi
}
BASH
    ],

    // --- 4.3.7 Ensure nftables outbound and established connections are configured ---
    [
        'id' => '4.3.7', 'title' => 'Ensure nftables outbound and established connections are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        a_output_info+=("  - Package 'nftables' is installed. Reviewing connection state rules...")
        
        ruleset=$(nft list ruleset)
        input_rules=$(echo "$ruleset" | awk '/hook input/,/}/')
        output_rules=$(echo "$ruleset" | awk '/hook output/,/}/')

        a_output_info+=("  - Input Chain Rules for Review:")
        a_output_info+=("------------------------------------")
        echo "$input_rules" | sed 's/^/    /'
        a_output_info+=("------------------------------------")
        
        a_output_info+=("  - Output Chain Rules for Review:")
        a_output_info+=("------------------------------------")
        echo "$output_rules" | sed 's/^/    /'
        a_output_info+=("------------------------------------")


        if echo "$input_rules" | grep -Pqs -- 'ct state\s+"?established"?\s+accept'; then
            a_output_pass+=("  - Input chain has a rule to accept 'established' state connections.")
        else
            a_output_fail+=("  - Input chain is missing a rule to accept 'established' state connections.")
        fi

        if echo "$output_rules" | grep -Pqs -- 'ct state\s+"?established,related,new"?\s+accept'; then
             a_output_pass+=("  - Output chain has a rule to accept 'established,related,new' state connections.")
        else
             a_output_fail+=("  - Output chain is missing a rule to accept 'established,related,new' state connections.")
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
            printf '%s\n' "Add rules to your nftables ruleset to allow established and outbound connections."
            printf '%s\n' "Example:"
            printf '%s\n' '# sudo nft add rule inet filter input ct state established accept'
            printf '%s\n' '# sudo nft add rule inet filter output ct state established,related,new accept'
        fi
    fi
}
BASH
    ],

    // --- 4.3.8 Ensure nftables default deny firewall policy ---
    [
        'id' => '4.3.8', 'title' => 'Ensure nftables default deny firewall policy', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        a_output_info+=("  - Package 'nftables' is installed. Checking base chain policies...")
        
        ruleset=$(nft list ruleset)

        if echo "$ruleset" | grep 'hook input' | grep -q 'policy drop;'; then
            a_output_pass+=("  - Base chain 'input' has a default policy of DROP.")
        else
            a_output_fail+=("  - Base chain 'input' does not have a default policy of DROP.")
        fi

        if echo "$ruleset" | grep 'hook forward' | grep -q 'policy drop;'; then
            a_output_pass+=("  - Base chain 'forward' has a default policy of DROP.")
        else
            a_output_fail+=("  - Base chain 'forward' does not have a default policy of DROP.")
        fi

        if echo "$ruleset" | grep 'hook output' | grep -q 'policy drop;'; then
            a_output_pass+=("  - Base chain 'output' has a default policy of DROP.")
        else
            a_output_fail+=("  - Base chain 'output' does not have a default policy of DROP.")
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
            printf '%s\n' "Run the following commands to set a default DROP policy on the base chains."
            printf '%s\n' "Note: This may interrupt existing connections. Apply during a maintenance window."
            printf '%s\n' '# sudo nft chain inet filter input { policy drop \; }'
            printf '%s\n' '# sudo nft chain inet filter forward { policy drop \; }'
            printf '%s\n' '# sudo nft chain inet filter output { policy drop \; }'
        fi
    fi
}
BASH
    ],

    // --- 4.3.9 Ensure nftables service is enabled ---
    [
        'id' => '4.3.9', 'title' => 'Ensure nftables service is enabled', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()
    SERVICE_NAME="nftables.service"

    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
    else
        a_output_info+=("  - Package 'nftables' is installed. Checking service status...")
        
        enable_status=$(systemctl is-enabled "$SERVICE_NAME" 2>/dev/null)

        if [ "$enable_status" == "enabled" ]; then
            a_output_pass+=("  - '$SERVICE_NAME' is correctly enabled.")
        else
            a_output_fail+=("  - '$SERVICE_NAME' is not enabled (current status: '$enable_status').")
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

            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "Run the following command to enable the nftables service:"
            printf '%s\n' "# sudo systemctl enable $SERVICE_NAME"
        fi
    fi
}
BASH
    ],

    // --- 4.3.10 Ensure nftables rules are permanent ---
    [
        'id' => '4.3.10', 'title' => 'Ensure nftables rules are permanent', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if ! dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Package 'nftables' is not installed."
        printf '%s\n' "  - This check is not applicable."
    else
        
        ruleset=$(nft list ruleset)
        
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "Please review the following chains to ensure they match your site policy."

        printf '\n%s\n' "--- INPUT Chain ---"
        echo "$ruleset" | awk '/hook input/,/}/'
        printf '%s\n' "-------------------"
        
        printf '\n%s\n' "--- FORWARD Chain ---"
        echo "$ruleset" | awk '/hook forward/,/}/'
        printf '%s\n' "---------------------"

        printf '\n%s\n' "--- OUTPUT Chain ---"
        echo "$ruleset" | awk '/hook output/,/}/'
        printf '%s\n' "--------------------"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "Ensure your ruleset is saved to be persistent on boot."
        printf '%s\n' "Example command to save the current ruleset:"
        printf '%s\n' "# sudo nft list ruleset > /etc/nftables.conf"

        printf '\n%s\n' "** MANUAL ** Required: Manually review the output above against your site policy."
    fi
}
BASH
    ],

    // --- 4.4 Configure iptables ---
    [ 'id' => '4.4', 'title' => 'Configure iptables', 'type' => 'header' ],

    // --- 4.4.1 Configure iptables software ---
    [ 'id' => '4.4.1', 'title' => 'Configure iptables software', 'type' => 'header' ],

    // --- 4.4.1.1 Ensure iptables packages are installed ---
    [
        'id' => '4.4.1.1', 'title' => 'Ensure iptables packages are installed', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_info+=("  - Note: iptables is being phased out. It is recommended to transition to nftables or ufw.")

        if dpkg-query -s "iptables" &>/dev/null; then
            a_output_pass+=("  - Package 'iptables' is installed.")
        else
            a_output_fail+=("  - Package 'iptables' is not installed.")
        fi

        if dpkg-query -s "iptables-persistent" &>/dev/null; then
            a_output_pass+=("  - Package 'iptables-persistent' is installed.")
        else
            a_output_fail+=("  - Package 'iptables-persistent' is not installed.")
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
            printf '%s\n' "Run the following command to install iptables and iptables-persistent:"
            printf '%s\n' "# sudo apt install iptables iptables-persistent"
        fi
    fi
}
BASH
    ],

    // --- 4.4.1.2 Ensure nftables is not in use with iptables ---
    [
        'id' => '4.4.1.2', 'title' => 'Ensure nftables is not in use with iptables', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check is not applicable as iptables is not the configured firewall."
    else
        printf '%s\n' "" "- Audit Result:" "  ** PASS **"
        printf '%s\n' "  - 'iptables' is the default firewall and 'nftables' is not installed."
    fi
}
BASH
    ],

    // --- 4.4.1.3 Ensure ufw is not in use with iptables ---
    [
        'id' => '4.4.1.3', 'title' => 'Ensure ufw is not in use with iptables', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        if ! dpkg-query -s "ufw" &>/dev/null; then
            a_output_pass+=("  - Package 'ufw' is not installed, which is the correct state when using iptables as the primary firewall.")
        else
            a_output_fail+=("  - Inconsistency found: 'ufw' package is installed but was not detected as a primary firewall.")
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
        fi
    fi
}
BASH
    ],

    // --- 4.4.2 Configure IPv4 iptables ---
    [ 'id' => '4.4.2', 'title' => 'Configure IPv4 iptables', 'type' => 'header' ],

    // --- 4.4.2.1 Ensure iptables default deny firewall policy ---
    [
        'id' => '4.4.2.1', 'title' => 'Ensure iptables default deny firewall policy', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        a_output_info+=("  - iptables appears to be the primary firewall. Auditing default policies...")

        input_policy=$(iptables -L INPUT | grep -oP '(?<=policy )\w+')
        forward_policy=$(iptables -L FORWARD | grep -oP '(?<=policy )\w+')
        output_policy=$(iptables -L OUTPUT | grep -oP '(?<=policy )\w+')

        if [[ "$input_policy" == "DROP" || "$input_policy" == "REJECT" ]]; then
            a_output_pass+=("  - INPUT chain default policy is compliant ('$input_policy').")
        else
            a_output_fail+=("  - INPUT chain default policy is '$input_policy', but should be DROP or REJECT.")
        fi

        if [[ "$forward_policy" == "DROP" || "$forward_policy" == "REJECT" ]]; then
            a_output_pass+=("  - FORWARD chain default policy is compliant ('$forward_policy').")
        else
            a_output_fail+=("  - FORWARD chain default policy is '$forward_policy', but should be DROP or REJECT.")
        fi

        if [[ "$output_policy" == "DROP" || "$output_policy" == "REJECT" ]]; then
            a_output_pass+=("  - OUTPUT chain default policy is compliant ('$output_policy').")
        else
            a_output_fail+=("  - OUTPUT chain default policy is '$output_policy', but should be DROP or REJECT.")
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
            printf '%s\n' "Run the following commands to implement a default DROP policy:"
            printf '%s\n' "# sudo iptables -P INPUT DROP"
            printf '%s\n' "# sudo iptables -P FORWARD DROP"
            printf '%s\n' "# sudo iptables -P OUTPUT DROP"
        fi
    fi
}
BASH
    ],

    // --- 4.4.2.2 Ensure iptables loopback traffic is configured ---
    [
        'id' => '4.4.2.2', 'title' => 'Ensure iptables loopback traffic is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    a_output_pass=()
    a_output_fail=()
    a_output_info=()

    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_info+=("  - iptables appears to be the primary firewall. Auditing loopback rules...")

        if iptables -S INPUT | grep -q -- "-i lo -j ACCEPT"; then
            a_output_pass+=("  - An INPUT rule exists to ACCEPT traffic on the loopback interface 'lo'.")
        else
            a_output_fail+=("  - An INPUT rule to ACCEPT traffic on the loopback interface 'lo' is missing.")
        fi

        if iptables -S OUTPUT | grep -q -- "-o lo -j ACCEPT"; then
            a_output_pass+=("  - An OUTPUT rule exists to ACCEPT traffic on the loopback interface 'lo'.")
        else
            a_output_fail+=("  - An OUTPUT rule to ACCEPT traffic on the loopback interface 'lo' is missing.")
        fi

        if iptables -S INPUT | grep -q -- "-s 127.0.0.0/8 -j DROP"; then
            a_output_pass+=("  - An INPUT rule exists to DROP traffic from the loopback network (127.0.0.0/8).")
        else
            a_output_fail+=("  - An INPUT rule to DROP traffic from the loopback network (127.0.0.0/8) is missing.")
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
            printf '%s\n' "Run the following commands to implement the required loopback rules:"
            printf '%s\n' "# sudo iptables -A INPUT -i lo -j ACCEPT"
            printf '%s\n' "# sudo iptables -A OUTPUT -o lo -j ACCEPT"
            printf '%s\n' "# sudo iptables -A INPUT -s 127.0.0.0/8 -j DROP"
        fi
    fi
}
BASH
    ],

    // --- 4.4.2.3 Ensure iptables outbound and established connections are configured ---
    [
        'id' => '4.4.2.3', 'title' => 'Ensure iptables outbound and established connections are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        printf '%s\n' "" "-- INFO --"
        printf '%s\n' "Review the following iptables rules to ensure they match your site policy."
        printf '%s\n' "--------------------------------------------------------------------------------"
        
        iptables -L -v -n
        
        printf '%s\n' "--------------------------------------------------------------------------------"
        
        printf '\n\n%s\n' "-- Suggestion --"
        printf '%s\n' "To implement a default policy that allows all outbound and established connections,"
        printf '%s\n' "run the following commands:"
        printf '%s\n' "# sudo iptables -A OUTPUT -p tcp -m state --state NEW,ESTABLISHED -j ACCEPT"
        printf '%s\n' "# sudo iptables -A OUTPUT -p udp -m state --state NEW,ESTABLISHED -j ACCEPT"
        printf '%s\n' "# sudo iptables -A INPUT -p tcp -m state --state ESTABLISHED -j ACCEPT"
        printf '%s\n' "# sudo iptables -A INPUT -p udp -m state --state ESTABLISHED -j ACCEPT"
        
        printf '\n%s\n' "** REVIEW ** Required: Manually review the output above against your site policy."
    fi
}
BASH
    ],

    // --- 4.4.2.4 Ensure iptables firewall rules exist for all open ports  ---
    [
        'id' => '4.4.2.4', 'title' => 'Ensure iptables firewall rules exist for all open ports ', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        a_output_info+=("  - iptables appears to be the primary firewall. Auditing open ports against rules...")

        open_ports=$(ss -4tuln | awk '($5!~/%lo:/ && $5!~/127.0.0.1:/) {split($5, a, ":"); print a[length(a)]}' | sort -u)
        
        ruled_ports=$(iptables -S INPUT | grep -- '-p tcp -m state --state NEW -j ACCEPT' | grep -oP '(?<=--dport )\d+' | sort -u)

        a_output_info+=("  - Open Ports Found: $(echo "$open_ports" | tr '\n' ' ')")
        a_output_info+=("  - Ports with ACCEPT rules: $(echo "$ruled_ports" | tr '\n' ' ')")

        unruled_ports=$(comm -23 <(echo "$open_ports") <(echo "$ruled_ports"))

        if [ -z "$unruled_ports" ]; then
            a_output_pass+=("  - All open network ports are covered by iptables rules.")
        else
            while IFS= read -r port; do
                a_output_fail+=("  - Open port '$port' has no corresponding ACCEPT rule for new connections.")
            done <<< "$unruled_ports"
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
            printf '%s\n' "For each un-ruled port listed above, create an appropriate firewall rule."
            printf '%s\n' "Example command to allow new TCP connections for a port:"
            printf '%s\n' "# sudo iptables -A INPUT -p tcp --dport <port_number> -m state --state NEW -j ACCEPT"
        fi
    fi
}
BASH
    ],

    // --- 4.4.3 Configure IPv6 ip6tables ---
    [ 'id' => '4.4.3', 'title' => 'Configure IPv6 iptables', 'type' => 'header' ],

    // --- 4.4.3.1 Ensure ip6tables default deny firewall policy ---
    [
        'id' => '4.4.3.1', 'title' => 'Ensure ip6tables default deny firewall policy', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        ipv6_disabled="no"
        if [ -f /sys/module/ipv6/parameters/disable ] && grep -q "1" /sys/module/ipv6/parameters/disable; then
            ipv6_disabled="yes"
        elif /sbin/sysctl -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1" && \
             /sbin/sysctl -n net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -q "1"; then
            ipv6_disabled="yes"
        fi

        if [ "$ipv6_disabled" == "yes" ]; then
             printf '%s\n' "" "- Audit Result:" "  ** PASS **"
             printf '%s\n' "  - IPv6 is disabled on the system, this check is not applicable."
        else
            a_output_info+=("  - iptables is the primary firewall and IPv6 is enabled. Auditing default policies...")

            input_policy=$(ip6tables -L INPUT | grep -oP '(?<=policy )\w+')
            forward_policy=$(ip6tables -L FORWARD | grep -oP '(?<=policy )\w+')
            output_policy=$(ip6tables -L OUTPUT | grep -oP '(?<=policy )\w+')

            if [[ "$input_policy" == "DROP" || "$input_policy" == "REJECT" ]]; then
                a_output_pass+=("  - INPUT chain default policy is compliant ('$input_policy').")
            else
                a_output_fail+=("  - INPUT chain default policy is '$input_policy', but should be DROP or REJECT.")
            fi

            if [[ "$forward_policy" == "DROP" || "$forward_policy" == "REJECT" ]]; then
                a_output_pass+=("  - FORWARD chain default policy is compliant ('$forward_policy').")
            else
                a_output_fail+=("  - FORWARD chain default policy is '$forward_policy', but should be DROP or REJECT.")
            fi

            if [[ "$output_policy" == "DROP" || "$output_policy" == "REJECT" ]]; then
                a_output_pass+=("  - OUTPUT chain default policy is compliant ('$output_policy').")
            else
                a_output_fail+=("  - OUTPUT chain default policy is '$output_policy', but should be DROP or REJECT.")
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
                printf '%s\n' "Run the following commands to implement a default DROP policy:"
                printf '%s\n' "# sudo ip6tables -P INPUT DROP"
                printf '%s\n' "# sudo ip6tables -P FORWARD DROP"
                printf '%s\n' "# sudo ip6tables -P OUTPUT DROP"
            fi
        fi
    fi
}
BASH
    ],

    // --- 4.4.3.2 Ensure ip6tables loopback traffic is configured ---
    [
        'id' => '4.4.3.2', 'title' => 'Ensure ip6tables loopback traffic is configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        ipv6_disabled="no"
        if [ -f /sys/module/ipv6/parameters/disable ] && grep -q "1" /sys/module/ipv6/parameters/disable; then
            ipv6_disabled="yes"
        elif /sbin/sysctl -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1" && \
             /sbin/sysctl -n net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -q "1"; then
            ipv6_disabled="yes"
        fi

        if [ "$ipv6_disabled" == "yes" ]; then
             printf '%s\n' "" "- Audit Result:" "  ** PASS **"
             printf '%s\n' "  - IPv6 is disabled on the system, this check is not applicable."
        else
            a_output_info+=("  - iptables is the primary firewall and IPv6 is enabled. Auditing loopback rules...")

            if ip6tables -S INPUT | grep -q -- '-i lo -j ACCEPT'; then
                a_output_pass+=("  - An INPUT rule exists to ACCEPT traffic on the loopback interface 'lo'.")
            else
                a_output_fail+=("  - An INPUT rule to ACCEPT traffic on the loopback interface 'lo' is missing.")
            fi

            if ip6tables -S OUTPUT | grep -q -- '-o lo -j ACCEPT'; then
                a_output_pass+=("  - An OUTPUT rule exists to ACCEPT traffic on the loopback interface 'lo'.")
            else
                a_output_fail+=("  - An OUTPUT rule to ACCEPT traffic on the loopback interface 'lo' is missing.")
            fi

            if ip6tables -S INPUT | grep -q -- '-s ::1/128 -j DROP'; then
                a_output_pass+=("  - An INPUT rule exists to DROP traffic from the IPv6 loopback address (::1).")
            else
                a_output_fail+=("  - An INPUT rule to DROP traffic from the IPv6 loopback address (::1) is missing.")
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
                printf '%s\n' "Run the following commands to implement the required IPv6 loopback rules:"
                printf '%s\n' "# sudo ip6tables -A INPUT -i lo -j ACCEPT"
                printf '%s\n' "# sudo ip6tables -A OUTPUT -o lo -j ACCEPT"
                printf '%s\n' "# sudo ip6tables -A INPUT -s ::1 -j DROP"
            fi
        fi
    fi
}
BASH
    ],

    // --- 4.4.3.3 Ensure ip6tables outbound and established connections are configured ---
    [
        'id' => '4.4.3.3', 'title' => 'Ensure ip6tables outbound and established connections are configured', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        ipv6_disabled="no"
        if [ -f /sys/module/ipv6/parameters/disable ] && grep -q "1" /sys/module/ipv6/parameters/disable; then
            ipv6_disabled="yes"
        elif /sbin/sysctl -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1" && \
             /sbin/sysctl -n net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -q "1"; then
            ipv6_disabled="yes"
        fi

        if [ "$ipv6_disabled" == "yes" ]; then
             printf '%s\n' "" "- Audit Result:" "  ** PASS **"
             printf '%s\n' "  - IPv6 is disabled on the system, this check is not applicable."
        else
            printf '%s\n' "" "-- INFO --"
            printf '%s\n' "Review the following ip6tables rules to ensure they match your site policy."
            printf '%s\n' "--------------------------------------------------------------------------------"
            
            ip6tables -L -v -n
            
            printf '%s\n' "--------------------------------------------------------------------------------"
            
            printf '\n\n%s\n' "-- Suggestion --"
            printf '%s\n' "To implement a default policy that allows all outbound and established connections,"
            printf '%s\n' "run the following commands:"
            printf '%s\n' "# sudo ip6tables -A OUTPUT -p tcp -m state --state NEW,ESTABLISHED -j ACCEPT"
            printf '%s\n' "# sudo ip6tables -A OUTPUT -p udp -m state --state NEW,ESTABLISHED -j ACCEPT"
            printf '%s\n' "# sudo ip6tables -A INPUT -p tcp -m state --state ESTABLISHED -j ACCEPT"
            printf '%s\n' "# sudo ip6tables -A INPUT -p udp -m state --state ESTABLISHED -j ACCEPT"
            
            printf '\n%s\n' "** MANUAL ** Required: Manually review the output above against your site policy."
        fi
    fi
}
BASH
    ],

    // --- 4.4.3.4 Ensure ip6tables firewall rules exist for all open ports ---
    [
        'id' => '4.4.3.4', 'title' => 'Ensure ip6tables firewall rules exist for all open ports', 'profile' => 'Level 1 - Server', 'type' => 'Automated',        
	'requires_root' => false, 'audit_script' => <<<'BASH'
#!/usr/bin/env bash
{
    if dpkg-query -s "ufw" &>/dev/null || dpkg-query -s "nftables" &>/dev/null; then
        printf '%s\n' "" "- Audit Result:" "  ** SKIPPED **"
        printf '%s\n' "  - SKIPPED: Another firewall (UFW or nftables) is installed."
        printf '%s\n' "  - This check for iptables is not applicable."
    else
        a_output_pass=()
        a_output_fail=()
        a_output_info=()

        ipv6_disabled="no"
        if [ -f /sys/module/ipv6/parameters/disable ] && grep -q "1" /sys/module/ipv6/parameters/disable; then
            ipv6_disabled="yes"
        elif /sbin/sysctl -n net.ipv6.conf.all.disable_ipv6 2>/dev/null | grep -q "1" && \
             /sbin/sysctl -n net.ipv6.conf.default.disable_ipv6 2>/dev/null | grep -q "1"; then
            ipv6_disabled="yes"
        fi

        if [ "$ipv6_disabled" == "yes" ]; then
             printf '%s\n' "" "- Audit Result:" "  ** PASS **"
             printf '%s\n' "  - IPv6 is disabled on the system, this check is not applicable."
        else
            a_output_info+=("  - iptables is the primary firewall and IPv6 is enabled. Auditing open ports against rules...")

            open_ports=$(ss -6tuln | awk '($5!~/lo:/) {split($5, a, ":"); print a[length(a)]}' | sort -u)
            ruled_ports=$(ip6tables -S INPUT | grep -- '-p tcp -m state --state NEW -j ACCEPT' | grep -oP '(?<=--dport )\d+' | sort -u)

            a_output_info+=("  - Open IPv6 Ports Found: $(echo "$open_ports" | tr '\n' ' ')")
            a_output_info+=("  - IPv6 Ports with ACCEPT rules: $(echo "$ruled_ports" | tr '\n' ' ')")

            unruled_ports=$(comm -23 <(echo "$open_ports") <(echo "$ruled_ports"))

            if [ -z "$unruled_ports" ]; then
                a_output_pass+=("  - All open IPv6 network ports are covered by ip6tables rules.")
            else
                while IFS= read -r port; do
                    a_output_fail+=("  - Open IPv6 port '$port' has no corresponding ACCEPT rule for new connections.")
                done <<< "$unruled_ports"
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
                printf '%s\n' "For each un-ruled port, create an appropriate firewall rule."
                printf '%s\n' "Example command to allow new TCP connections for a port:"
                printf '%s\n' "# sudo ip6tables -A INPUT -p tcp --dport <port_number> -m state --state NEW -j ACCEPT"
            fi
        fi
    fi
}
BASH
    ],

];
