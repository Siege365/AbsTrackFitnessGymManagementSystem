import os
import re

files_to_fix = [
    'resources/views/pages/samples/error-404.blade.php',
    'resources/views/pages/samples/login.blade.php',
    'resources/views/pages/samples/register.blade.php',
    'resources/views/pages/samples/blank-page.blade.php'
]

for file_path in files_to_fix:
    if not os.path.exists(file_path):
        print(f'⚠️  {file_path} not found')
        continue
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Fix missing <body> tag
    content = re.sub(
        r'</head>\s*<div class="container-scroller">',
        r'</head>\n<body>\n    <div class="container-scroller">',
        content
    )
    
    # Fix relative script paths
    content = re.sub(
        r'src="../../assets/',
        r'src="{{ asset(\'template/assets/',
        content
    )
    content = re.sub(
        r'\.js"',
        r'.js\') }}"',
        content
    )
    
    # Fix year in copyright
    content = re.sub(
        r'Copyright &copy; 2020',
        r'Copyright &copy; {{ date(\'Y\') }}',
        content
    )
    
    # Fix escaped quotes in url()
    content = content.replace(r"url(\'/\')", "url('/')")
    
    # Ensure closing body tag before html
    if '</body>' not in content:
        content = re.sub(
            r'(<!-- endinject -->)\s*(</html>)',
            r'\1\n</body>\n\2',
            content
        )
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f'✅ Fixed {file_path}')

print('\n✅ All sample pages fixed!')
