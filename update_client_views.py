#!/usr/bin/env python3
"""
Update clients views to replace membership references with client references
"""

import re
import os

def update_file(filepath, replacements):
    """Update file with multiple string replacements"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        for old, new in replacements:
            content = content.replace(old, new)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"✅ Updated: {filepath}")
        return True
    except Exception as e:
        print(f"❌ Error updating {filepath}: {e}")
        return False

# Define replacements for index.blade.php
index_replacements = [
    ("'Customers → Membership'", "'Customers → Clients'"),
    ("Customers → Membership", "Customers → Clients"),
    ("$totalMembers", "$totalClients"),
    ("$activeMembers", "$activeClients"),
    ("Total Members", "Total Clients"),
    ("Active Members", "Active Clients"),
    ("List Of Memberships", "List Of Clients"),
    ("memberships.create", "clients.create"),
    ("Add Member", "Add Client"),
    ("$memberships as $membership", "$clients as $client"),
    ("$membership->id", "$client->id"),
    ("$membership->avatar", "$client->avatar"),
    ("$membership->name", "$client->name"),
    ("$membership->plan_type", "$client->plan_type"),
    ("$membership->start_date", "$client->start_date"),
    ("$membership->due_date", "$client->due_date"),
    ("$membership->status", "$client->status"),
    ("$membership->contact", "$client->contact"),
    ("memberships.destroy", "clients.destroy"),
    ("memberships.update", "clients.update"),
    ("'Are you sure you want to delete this membership?'", "'Are you sure you want to delete this client?'"),
    ("No memberships found", "No clients found"),
    ("Add your first member", "Add your first client"),
    ("$memberships->links()", "$clients->links()"),
    ("Edit Member", "Edit Client"),
    ("viewModal", "viewModalClient"),
    ("viewModalLabel", "viewModalLabelClient"),
    ("previewAvatar(", "previewClientAvatar("),
    ("function previewAvatar(membershipId)", "function previewClientAvatar(clientId)"),
    ("avatarInput' + membershipId", "avatarInputClient' + clientId"),
    ("avatarPreview' + membershipId", "avatarPreviewClient' + clientId"),
]

# Define replacements for create.blade.php
create_replacements = [
    ("'Add New Member'", "'Add New Client'"),
    ("Add New Member", "Add New Client"),
    ("Memberships", "Clients"),
    ("memberships.store", "clients.store"),
    ("memberships.index", "clients.index"),
    ("Member Name", "Client Name"),
    ("Enter member name", "Enter client name"),
    ("Add Member", "Add Client"),
]

# Define replacements for edit.blade.php
edit_replacements = [
    ("'Edit Member'", "'Edit Client'"),
    ("Edit Member", "Edit Client"),
    ("Memberships", "Clients"),
    ("memberships.update", "clients.update"),
    ("memberships.index", "clients.index"),
    ("$membership", "$client"),
    ("Member Name", "Client Name"),
    ("Enter member name", "Enter client name"),
    ("Update Member", "Update Client"),
]

# Update files
base_path = "resources/views/clients"

print("\n" + "="*50)
print("Updating Client Views")
print("="*50 + "\n")

# Update index.blade.php
update_file(os.path.join(base_path, "index.blade.php"), index_replacements)

# Update create.blade.php
update_file(os.path.join(base_path, "create.blade.php"), create_replacements)

# Update edit.blade.php
update_file(os.path.join(base_path, "edit.blade.php"), edit_replacements)

print("\n" + "="*50)
print("✅ All client views updated successfully!")
print("="*50 + "\n")
