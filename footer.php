<?php
$total = get_total();
?>
    <div class="page-up">
        <a href="javascript:void(0)" id="scrollToTopButton"><span class="arrow_carrot-up"></span></a>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="footer__logo">
                    <a href="./index.html"><img src="/img/logo.png" alt=""></a>
                </div>
                <?php if ($total): ?>
                    <p style="margin-top: 10px">Hiện tại các game trên web đã có tổng cộng <?php echo number_format(intval($total->total_views), 0, ",", ".") ?> lượt xem và <?php echo number_format(intval($total->total_downloads), 0, ",", ".") ?> lượt tải. Cảm ơn các bạn rất nhiều!</p>
                <?php endif ?>
            </div>
            <div class="col-lg-6">
                <div class="footer__nav">
                    <ul>
                        <li class="active"><a href="/">Trang Chủ</a></li>
                        <li><a href="/games">Danh Sách Game</a></li>
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="https://www.facebook.com/groups/nobihazavietnam">Facebook</a></li>
                        <li><a href="https://discord.gg/QpMuX3gQ5u">Discord</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3">
                <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                  Developed in 2024 by <a href="https://s1432.org" target="_blank">Serena1432</a> | Designed with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>.<br>
                  Xem mã nguồn của website này ở trên <a href="https://github.com/Serena1432/NobihazaVietnamCollection" target="_blank">GitHub</a>.
                  <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>

              </div>
          </div>
      </div>
