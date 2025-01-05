<?php

// 设置正确的发布密码
$correctPassword = 'root'; // 请替换为您希望设置的密码
// 使用哈希密码验证
// $correctPasswordHash = password_hash('root', PASSWORD_DEFAULT); // 推荐用密码哈希存储

// 初始化错误消息
$errorMessage = '';

// 读取现有的博客内容
$file = 'blog.txt';
$blogEntries = [];

if (file_exists($file)) {
    $currentContent = file_get_contents($file);
    // 使用新的分隔符解析博客
    $rawEntries = explode('@', $currentContent);

    foreach ($rawEntries as $entry) {
        $entry = trim($entry); // 去掉首尾空白
        if (!empty($entry) && str_contains($entry, '￥￥￥')) {
            $blogEntries[] = "@" . $entry; // 保留分隔符
        }
    }
}

// 检查是否有 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 发布新博客
    if (isset($_POST['title']) && isset($_POST['content']) && isset($_POST['password'])) {
        $title = $_POST['title'];
        $content = $_POST['content']; // 使用内容内容
        $password = $_POST['password'];
        $date = date('Y.m.d H:i:s'); // 获取当前日期

        if (!empty($title) && !empty($content)) { // 修正这里，直接使用 $_POST['content']
            if ($password === $correctPassword) { // 如果用哈希密码，使用 password_verify($password, $correctPasswordHash)
                // 格式化博客内容
                $blogEntry = "@\n标题：$title\n时间：$date\n内容：\n$content\n￥￥￥";

                // 确保现有博客内容末尾有换行符，用于拼接新博客
                $currentContent = implode("\n", $blogEntries);
                if (!empty($currentContent) && !str_ends_with($currentContent, "\n")) {
                    $currentContent .= "\n";
                }

                // 添加新博客
                file_put_contents($file, $currentContent . $blogEntry . "\n");
                $errorMessage = '发布成功！';
                header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($errorMessage));
                exit;
            } else {
                $errorMessage = '密码错误！';
                header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($errorMessage));
                exit;
            }
        } else {
            $errorMessage = '标题或内容不能为空！';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($errorMessage));
            exit;
        }
    }

    // 删除博客
    if (isset($_POST['delete_index'])) {
        $deleteIndex = $_POST['delete_index'];
        $deletePassword = $_POST['delete_password'];

        if ($deletePassword === $correctPassword) { // 使用哈希密码验证： password_verify($deletePassword, $correctPasswordHash)
            if (isset($blogEntries[$deleteIndex])) {
                unset($blogEntries[$deleteIndex]); // 删除指定博客
                $currentContent = implode("\n", $blogEntries); // 重新组合博客内容
                file_put_contents($file, $currentContent); // 保存到文件
                $errorMessage = '博客删除成功！';
                header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($errorMessage));
                exit;
            }
        } else {
            $errorMessage = '删除密码错误！';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?error=' . urlencode($errorMessage));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>发布博客</title>
  <style>
    /* 样式保持不变 */
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }

    .form-container, .blog-list, .delete-password-container {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 20px;
      margin-bottom: 20px;
    }

    h2 {
      color: #333;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    label {
      margin-top: 10px;
      margin-bottom: 5px;
    }

    input[type="text"], input[type="password"], textarea {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 20px;
    }

    input[type="submit"] {
      padding: 10px 20px;
      background-color: #5cb85c;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #4cae4c;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #f7f7f7;
    }

    button {
      padding: 10px 20px;
      background-color: #d9534f;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #c9302c;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>发布新博客</h2>
    <form action="" method="POST">
      <label for="title">标题：</label>
      <input type="text" id="title" name="title" required><br>

      <label for="content">内容：</label>
      <textarea id="content" name="content" rows="10" cols="50" required></textarea><br>

      <label for="password">发布密码：</label>
      <input type="password" id="password" name="password" required><br>

      <input type="submit" value="发布博客">
    </form>
  </div>

  <div class="blog-list">
    <h2>管理博客</h2>
    <table>
      <thead>
        <tr>
          <th>标题</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($blogEntries as $index => $entry): ?>
          <?php
          // 解析博客内容
          preg_match('/标题：(.+?)\n/', $entry, $titleMatch);
          $title = isset($titleMatch[1]) ? $titleMatch[1] : '';
          ?>
          <tr>
            <td><?php echo htmlspecialchars($title); ?></td>
            <td>
              <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="delete_index" value="<?php echo $index; ?>">
                <button type="button" onclick="showDeletePasswordForm(<?php echo $index; ?>)">删除</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="delete-password-container" id="delete-password-container" style="display: none;">
    <h2>输入删除密码：</h2>
    <form action="" method="POST">
      <input type="hidden" id="delete_index" name="delete_index" value="">
      <label for="delete_password">删除密码：</label>
      <input type="password" id="delete_password" name="delete_password" required><br>
      <input type="submit" value="删除博客">
    </form>
  </div>

  <script>
    // 弹窗提示
    <?php if (isset($_GET['error'])): ?>
      alert('<?php echo htmlspecialchars($_GET['error']); ?>');
    <?php endif; ?>

    // 显示删除密码输入框
    function showDeletePasswordForm(index) {
      document.getElementById('delete-password-container').style.display = 'block';
      document.getElementById('delete_index').value = index;
    }
  </script>

</body>
</html>