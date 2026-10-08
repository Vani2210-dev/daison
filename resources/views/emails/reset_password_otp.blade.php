<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực OTP - KBTech ERP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0c244b 0%, #091b38 100%); padding: 32px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">
                                KBTech <span style="color: #38bdf8;">ERP</span>
                            </h1>
                            <p style="margin: 6px 0 0 0; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;">
                                Hệ Thống Quản Trị Điện Nước & Ống Nhựa PVC
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 36px 32px 28px 32px;">
                            <h2 style="margin: 0 0 16px 0; color: #0f172a; font-size: 18px; font-weight: 700;">
                                Yêu cầu đặt lại mật khẩu tài khoản
                            </h2>
                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Xin chào <strong>{{ $user->name }}</strong> (Mã NV: <strong>{{ $user->code ?? "NV-{$user->id}" }}</strong>),
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Hệ thống nhận được yêu cầu khôi phục mật khẩu cho tài khoản liên kết với địa chỉ email này. Dưới đây là mã xác thực <strong>OTP</strong> (One-Time Password) của bạn:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f0f9ff; border: 2px dashed #0284c7; border-radius: 12px; padding: 20px; text-align: center; margin: 0 0 24px 0;">
                                <div style="font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                                    MÃ XÁC THỰC OTP
                                </div>
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #0c4a6e;">
                                    {{ $otp }}
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 8px;">
                                    Hiệu lực trong vòng <strong>15 phút</strong> kể từ khi tạo yêu cầu
                                </div>
                            </div>

                            <!-- Security Warning -->
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin: 0 0 24px 0;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #92400e;">
                                    <strong>Cảnh báo an toàn:</strong> Tuyệt đối không chia sẻ mã này cho bất kỳ ai, kể cả nhân viên kỹ thuật hoặc quản trị viên hệ thống.
                                </p>
                            </div>

                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #64748b;">
                                Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email hoặc thông báo ngay cho Ban Giám Đốc/Bộ phận IT để đảm bảo an toàn cho tài khoản.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0 0 4px 0;">Email tự động gửi từ <strong>Hệ Thống Quản Trị ERP KBTech</strong></p>
                            <p style="margin: 0;">Vui lòng không phản hồi trực tiếp vào địa chỉ email này.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
