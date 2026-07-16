<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

$errors = array();
$values = array(
    'make' => '', 'model' => '', 'year' => date('Y'), 'price' => '',
    'stock' => '', 'type' => '', 'transmission' => '', 'engine' => '',
    'fuel' => '', 'img' => '',
);
$editId = 0; 

// Pull a car row into $values for validation/re-display
function collect_car_input() {
    $v = array(
        'make'         => trim(isset($_POST['make'])         ? $_POST['make']         : ''),
        'model'        => trim(isset($_POST['model'])        ? $_POST['model']        : ''),
        'year'         => (int)  (isset($_POST['year'])       ? $_POST['year']         : 0),
        'price'        => (float)(isset($_POST['price'])      ? $_POST['price']        : 0),
        'stock'        => (int)  (isset($_POST['stock'])      ? $_POST['stock']        : 0),
        'type'         => trim(isset($_POST['type'])         ? $_POST['type']         : ''),
        'transmission' => trim(isset($_POST['transmission']) ? $_POST['transmission'] : ''),
        'engine'       => trim(isset($_POST['engine'])       ? $_POST['engine']       : ''),
        'fuel'         => trim(isset($_POST['fuel'])         ? $_POST['fuel']         : ''),
        'img'          => trim(isset($_POST['img'])          ? $_POST['img']          : ''),
    );
    return $v;
}

function validate_car($v) {
    $e = array();
    if ($v['make'] == '')                 $e['make']  = 'Brand is required.';
    if ($v['model'] == '')                $e['model'] = 'Model is required.';
    if ($v['year'] < 1900 || $v['year'] > (int)date('Y') + 1) $e['year'] = 'Enter a valid year.';
    if ($v['price'] <= 0)                 $e['price'] = 'Enter a price greater than 0.';
    if ($v['stock'] < 0)                  $e['stock'] = 'Stock cannot be negative.';
    if ($v['type'] == '')                 $e['type']  = 'Type is required.';
    if ($v['transmission'] == '')         $e['transmission'] = 'Transmission is required.';
    if ($v['engine'] == '')               $e['engine'] = 'Engine is required.';
    if ($v['fuel'] == '')                 $e['fuel']  = 'Fuel is required.';
    if ($v['img'] == '')                  $e['img']   = 'Image filename is required.';
    return $e;
}

// ── Handle actions ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'delete_car') {
        $carId = (int) (isset($_POST['car_id']) ? $_POST['car_id'] : 0);

        $stmt = mysqli_prepare($conn, "SELECT make, model FROM vehicles WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $carId);
        mysqli_stmt_execute($stmt);
        $car = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "DELETE FROM vehicles WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $carId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($car) {
            log_activity($conn, 'delete_car', $car['make'] . ' ' . $car['model']);
        }
        $_SESSION['flash']      = 'Vehicle deleted.';
        $_SESSION['flash_type'] = 'success';
        header('Location: cars.php');
        exit;
    }

    if ($action == 'add_car' || $action == 'update_car') {
        $values = collect_car_input();
        $errors = validate_car($values);
        $editId = (int) (isset($_POST['car_id']) ? $_POST['car_id'] : 0);

        if (empty($errors)) {
            if ($action == 'add_car') {
                $sql  = "INSERT INTO vehicles (make, model, year, price, type, transmission, engine, fuel, img, stock)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'ssidsssssi',
                    $values['make'], $values['model'], $values['year'], $values['price'],
                    $values['type'], $values['transmission'], $values['engine'],
                    $values['fuel'], $values['img'], $values['stock']
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                log_activity($conn, 'add_car', $values['make'] . ' ' . $values['model']);
                $_SESSION['flash']      = 'Vehicle "' . $values['make'] . ' ' . $values['model'] . '" added.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $sql  = "UPDATE vehicles
                         SET make = ?, model = ?, year = ?, price = ?, type = ?,
                             transmission = ?, engine = ?, fuel = ?, img = ?, stock = ?
                         WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'ssidsssssii',
                    $values['make'], $values['model'], $values['year'], $values['price'],
                    $values['type'], $values['transmission'], $values['engine'],
                    $values['fuel'], $values['img'], $values['stock'], $editId
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                log_activity($conn, 'update_car', $values['make'] . ' ' . $values['model']);
                $_SESSION['flash']      = 'Vehicle "' . $values['make'] . ' ' . $values['model'] . '" updated.';
                $_SESSION['flash_type'] = 'success';
            }
            header('Location: cars.php');
            exit;
        }
    }
}

if ($editId == 0 && isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM vehicles WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($row) {
        $values = $row;
    } else {
        $editId = 0; 
    }
}

$cars = array();
$result = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = $row;
}

$pageTitle = 'Catalogue';
$adminPage = 'cars';

require_once '../../includes/admin-header.php';
?>

<div class="admin-page-head">
    <div>
        <h1>Car Catalogue</h1>
        <p><?= count($cars) ?> vehicles listed</p>
    </div>
</div>

<!-- Catalogue table -->
<table class="admin-table">
    <thead>
        <tr>
            <th>Photo</th>
            <th>Vehicle</th>
            <th>Brand</th>
            <th>Type</th>
            <th>Year</th>
            <th>Stock</th>
            <th class="text-right">Price</th>
            <th class="text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cars as $car): ?>
        <tr>
            <td>
                <div style="
                    width:72px;aspect-ratio:4/3;border-radius:6px;flex-shrink:0;
                    background:url('/VAR-Cars/public/assets/images/<?= rawurlencode($car['img']) ?>') center/cover #1B2B34;
                    border:1px solid #3C4A56;
                "></div>
            </td>
            <td>
                <p style="font-weight:600;color:#ffffff;margin:0 0 2px;">
                    <?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p style="font-size:0.75rem;color:#555;margin:0;">
                    <?= htmlspecialchars($car['transmission'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </td>
            <td><?= htmlspecialchars($car['make'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($car['type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$car['year'] ?></td>
            <td>
                <?php if ((int)$car['stock'] <= 0): ?>
                    <span style="color:#c0392b;font-weight:600;">Sold out</span>
                <?php else: ?>
                    <?= (int)$car['stock'] ?>
                <?php endif; ?>
            </td>
            <td class="text-right" style="color:#fff;font-weight:600;"><?= fmt_price($car['price']) ?></td>
            <td class="text-right" style="white-space:nowrap;">
                <a href="cars.php?edit=<?= (int)$car['id'] ?>#car-form" class="admin-btn admin-btn--ghost" style="padding:0.35rem 0.7rem;font-size:0.8rem;">Edit</a>
                <form method="POST" action="cars.php" style="display:inline;"
                      onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($car['make'] . ' ' . $car['model']), ENT_QUOTES, 'UTF-8') ?>? This cannot be undone.');">
                    <input type="hidden" name="action" value="delete_car">
                    <input type="hidden" name="car_id" value="<?= (int)$car['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn--ghost" style="padding:0.35rem 0.7rem;font-size:0.8rem;color:#e74c3c;border-color:#e74c3c;">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- Add / Edit form -->
<div id="car-form" style="background:#1B2B34;border:1px solid #3C4A56;border-radius:12px;padding:1.5rem;margin-bottom:2rem;">
    <h2 style="margin:0 0 1rem;font-size:1.05rem;color:#fff;">
        <?= $editId ? 'Edit Vehicle #' . (int)$editId : 'Add a Vehicle' ?>
    </h2>
    <form method="POST" action="cars.php<?= $editId ? '?edit=' . (int)$editId : '' ?>" novalidate>
        <input type="hidden" name="action" value="<?= $editId ? 'update_car' : 'add_car' ?>">
        <?php if ($editId): ?><input type="hidden" name="car_id" value="<?= (int)$editId ?>"><?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="make">Brand <span>*</span></label>
                <input class="form-input <?= isset($errors['make']) ? 'error' : '' ?>" type="text" id="make" name="make"
                       value="<?= htmlspecialchars($values['make'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['make'])): ?><span class="form-error"><?= htmlspecialchars($errors['make'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="model">Model <span>*</span></label>
                <input class="form-input <?= isset($errors['model']) ? 'error' : '' ?>" type="text" id="model" name="model"
                       value="<?= htmlspecialchars($values['model'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['model'])): ?><span class="form-error"><?= htmlspecialchars($errors['model'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="year">Year <span>*</span></label>
                <input class="form-input <?= isset($errors['year']) ? 'error' : '' ?>" type="number" id="year" name="year"
                       value="<?= htmlspecialchars($values['year'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['year'])): ?><span class="form-error"><?= htmlspecialchars($errors['year'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="price">Price (₱) <span>*</span></label>
                <input class="form-input <?= isset($errors['price']) ? 'error' : '' ?>" type="number" step="0.01" id="price" name="price"
                       value="<?= htmlspecialchars($values['price'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['price'])): ?><span class="form-error"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="stock">Stock <span>*</span></label>
                <input class="form-input <?= isset($errors['stock']) ? 'error' : '' ?>" type="number" id="stock" name="stock"
                       value="<?= htmlspecialchars($values['stock'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['stock'])): ?><span class="form-error"><?= htmlspecialchars($errors['stock'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="type">Type <span>*</span></label>
                <input class="form-input <?= isset($errors['type']) ? 'error' : '' ?>" type="text" id="type" name="type"
                       placeholder="Sedan, SUV, Coupe…"
                       value="<?= htmlspecialchars($values['type'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['type'])): ?><span class="form-error"><?= htmlspecialchars($errors['type'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="transmission">Transmission <span>*</span></label>
                <input class="form-input <?= isset($errors['transmission']) ? 'error' : '' ?>" type="text" id="transmission" name="transmission"
                       value="<?= htmlspecialchars($values['transmission'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['transmission'])): ?><span class="form-error"><?= htmlspecialchars($errors['transmission'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="fuel">Fuel <span>*</span></label>
                <input class="form-input <?= isset($errors['fuel']) ? 'error' : '' ?>" type="text" id="fuel" name="fuel"
                       placeholder="Gasoline, Hybrid…"
                       value="<?= htmlspecialchars($values['fuel'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['fuel'])): ?><span class="form-error"><?= htmlspecialchars($errors['fuel'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="engine">Engine <span>*</span></label>
            <input class="form-input <?= isset($errors['engine']) ? 'error' : '' ?>" type="text" id="engine" name="engine"
                   value="<?= htmlspecialchars($values['engine'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errors['engine'])): ?><span class="form-error"><?= htmlspecialchars($errors['engine'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="img">Image filename <span>*</span></label>
            <input class="form-input <?= isset($errors['img']) ? 'error' : '' ?>" type="text" id="img" name="img"
                   placeholder="e.g. ST BMW X5.jpg (file must exist in assets/images)"
                   value="<?= htmlspecialchars($values['img'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="form-hint">Filename of an image already in <code>public/assets/images/</code>.</span>
            <?php if (isset($errors['img'])): ?><span class="form-error"><?= htmlspecialchars($errors['img'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>

        <div class="admin-actions" style="margin-top:0.5rem;">
            <button type="submit" class="admin-btn admin-btn--primary"><?= $editId ? 'Save Changes' : 'Add Vehicle' ?></button>
            <?php if ($editId): ?><a href="cars.php" class="admin-btn admin-btn--ghost">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>
<?php require_once '../../includes/admin-footer.php'; ?>
